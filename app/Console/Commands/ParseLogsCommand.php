<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ParseLogsCommand extends Command
{
    protected $signature = 'parse:logs';
    protected $description = 'Ambil dan parsing log dari NAS ke database';

    public function handle()
    {
        $this->info('Memulai parsing log dari NAS...');

        $nasBasePath = 'vmbackup@103.156.110.2:/volume1/BACKUP_SERVER/logmonitor/';
        $nasPort = 31227;
        $localDir = storage_path('logs');
        $vms = [
            'ciku'    => 'ciku_sys_monitor.log',
            'sakip'   => 'sakip_sys_monitor.log',
            'bappeda' => 'bappeda_sys_monitor.log',
            'ebang'   => 'ebang_sys_monitor.log',
            'sipla'   => 'sipla_sys_monitor.log',
            'kalhiro' => 'kalhiro_sys_monitor.log',
            'sisda' => 'sisda_sys_monitor.log',
        ];

        foreach ($vms as $vm => $filename) {
            $remote = $nasBasePath . $filename;
            $local = $localDir . "/{$filename}";

            $this->info("Mengambil log [{$vm}]...");
            $cmd = "rsync -az -e 'ssh -p {$nasPort}' {$remote} {$local}";
            exec($cmd, $output, $status);

            if ($status !== 0 || !file_exists($local)) {
                $this->error("Gagal mengambil file: {$filename} | Status: $status");
                continue;
            }

            $lastTimestamp = DB::table('vm_logs')->where('vm_name', $vm)->max('timestamp');
            $this->info("Timestamp terakhir [{$vm}]: " . ($lastTimestamp ?? 'belum ada'));

            $handle = fopen($local, 'r');
            if (!$handle) {
                $this->error("Gagal membuka file: {$filename}");
                continue;
            }

            $bulkInsert = [];
            $insideBlock = false;
            $blockBuffer = [];

            while (($line = fgets($handle)) !== false) {
                $line = trim($line);

                if (str_contains($line, 'System Monitoring Started')) {
                    $insideBlock = true;
                    $blockBuffer = [$line];
                    continue;
                }

                if ($insideBlock) {
                    $blockBuffer[] = $line;

                    if (str_contains($line, 'System Monitoring Completed')) {
                        // Proses satu blok
                        $entry = $this->parseBlock($blockBuffer, $vm, $lastTimestamp);
                        if ($entry) {
                            $bulkInsert[] = $entry;
                            $this->line("✔️ [{$vm}] Entry ditambahkan @ {$entry['timestamp']}");
                        }
                        $insideBlock = false;
                        $blockBuffer = [];
                    }
                }
            }

            fclose($handle);

            if (count($bulkInsert)) {
                DB::beginTransaction();
                foreach (array_chunk($bulkInsert, 500) as $chunk) {
                    DB::table('vm_logs')->upsert(
                        $chunk,
                        ['vm_name', 'timestamp'],
                        [
                            'cpu_1min', 'cpu_5min', 'cpu_15min',
                            'ram_total', 'ram_used', 'ram_free', 'ram_percent',
                            'disk_used', 'disk_available', 'disk_total', 'disk_percent',
                            'bandwidth_down', 'bandwidth_up',
                            'updated_at'
                        ]
                    );
                }
                DB::commit();
                $this->info("✅ [{$vm}] Total entry disimpan: " . count($bulkInsert));
            } else {
                $this->warn("⚠️ [{$vm}] Tidak ada data baru untuk disimpan.");
            }
        }

        $this->info("🚀 Semua log selesai diproses.");
    }

    protected function parseBlock(array $lines, string $vm, $lastTimestamp)
    {
        $temp = [
            'vm_name' => $vm,
            'updated_at' => now(),
        ];
        $timestamp = null;

        foreach ($lines as $line) {
            // CPU
            if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) - CPU Usage Estimate:.*1 min: (.*?)% \| 5 min: (.*?)% \| 15 min: (.*?)%/', $line, $cpu)) {
                $timestamp = Carbon::parse($cpu[1]);
                $temp['timestamp'] = $timestamp;
                $temp['cpu_1min'] = (float) $cpu[2];
                $temp['cpu_5min'] = (float) $cpu[3];
                $temp['cpu_15min'] = (float) $cpu[4];
            }

            // RAM
            if (preg_match('/RAM Usage:.*Total: (.*?)MB \| Used: (.*?)MB \| Free: (.*?)MB \| Usage: (.*?)%/', $line, $ram)) {
                $temp['ram_total'] = (float) $ram[1];
                $temp['ram_used'] = (float) $ram[2];
                $temp['ram_free'] = (float) $ram[3];
                $temp['ram_percent'] = (float) $ram[4];
            }

            // Disk
            if (preg_match('/Disk Usage: Used: ([\d.]+)([GT]) \| Available: ([\d.]+)([GT]) \| Total: ([\d.]+)([GT]) \| Usage: (\d+)%/', $line, $disk)) {
                $used = ($disk[2] === 'T') ? (float)$disk[1] * 1024 : (float)$disk[1];
                $available = ($disk[4] === 'T') ? (float)$disk[3] * 1024 : (float)$disk[3];
                $total = ($disk[6] === 'T') ? (float)$disk[5] * 1024 : (float)$disk[5];

                $temp['disk_used'] = $used;
                $temp['disk_available'] = $available;
                $temp['disk_total'] = $total;
                $temp['disk_percent'] = (float)$disk[7];
            }

            // Bandwidth
            if (preg_match('/Current Bandwidth Usage: ↓\s*([\d\.]+)?\s*Mbps\s*\|\s*↑\s*([\d\.]+)?\s*Mbps/', $line, $bw)) {
                $temp['bandwidth_down'] = (float) $bw[1];
                $temp['bandwidth_up'] = (float) $bw[2];
            }
        }

        if (!isset($temp['timestamp']) || ($lastTimestamp && $temp['timestamp'] <= $lastTimestamp)) {
            return null;
        }

        return $temp;
    }
}
