<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ParseEximLog extends Command
{
    protected $signature = 'exim:parse-nas';
    protected $description = 'Ambil log dari NAS dan parsing log email keluar ke database';

    public function handle()
    {
        $this->info('Memulai parsing log dari NAS...');

        // === Konfigurasi NAS ===
        $nasUser = 'vmbackup';
        $nasIP = '103.156.110.2';
        $nasPort = 31227;
        $nasRemotePath = '/volume1/BACKUP_SERVER/logmonitor/exim_mainlog.log';
        $localDir = storage_path('logs');
        $localFile = $localDir . '/exim_mainlog.log';

        // Buat direktori lokal jika belum ada
        if (!file_exists($localDir)) {
            mkdir($localDir, 0755, true);
        }

        // Jalankan rsync untuk ambil file dari NAS
        $this->info('Mengambil log dari NAS menggunakan rsync...');
        $rsyncCommand = "rsync -avz -e 'ssh -p {$nasPort}' {$nasUser}@{$nasIP}:{$nasRemotePath} {$localFile}";

        exec($rsyncCommand, $output, $resultCode);

        if ($resultCode !== 0) {
            $this->error('Gagal mengambil log dari NAS.');
            return;
        }

        $this->info('Log berhasil diambil. Memulai parsing...');

        // Baca file log
        $lines = file($localFile);
        $inserted = 0;

        foreach ($lines as $line) {
            // Contoh log baris pengiriman: 2025-04-21 11:22:33 1h9Akw-0002kC-8K => recipient@domain.com ...
                if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}).*?=>\s+\S+\s+<([^>]+)>.*<([^>]+)>/', $line, $matches)) {
                    $datetime = Carbon::parse($matches[1]);
                    $sender = $matches[2];
                    $recipient = $matches[3];
                
                    DB::table('mail_logs')->insertOrIgnore([
                        'sent_at' => $datetime,
                        'sender_email' => $sender,
                        'recipient_email' => $recipient,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                
                    $inserted++;
                }

        }

        $this->info("Parsing selesai. Total data masuk: $inserted");
    }
}
