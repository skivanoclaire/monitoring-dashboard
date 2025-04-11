<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class CheckDnsUptime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:dns-uptime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = DB::table('dns_records')->get();
    
        foreach ($records as $record) {
            $host = $record->subdomain;
    
            // Gunakan ping (Linux only), bisa juga pakai HTTP request
            $ping = exec("ping -c 1 -W 1 {$host} > /dev/null 2>&1; echo $?");
            $isUp = $ping === '0';
    
            DB::table('dns_records')->where('id', $record->id)->update([
                'is_up' => $isUp
            ]);
    
            $this->info($host . ' => ' . ($isUp ? 'UP' : 'DOWN'));
        }
    
        $this->info('Monitoring selesai.');
    }
}
