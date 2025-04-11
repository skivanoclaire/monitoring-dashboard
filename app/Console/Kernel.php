<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Daftar Artisan commands yang tersedia untuk aplikasi.
     */
    protected $commands = [
        // Tambahkan command kamu di sini kalau perlu daftar manual
        // \App\Console\Commands\SyncCloudflareDns::class,
    ];

    /**
     * Jadwal tugas Artisan.
     */
    protected function schedule(Schedule $schedule)
    {
        // Sync data DNS dari Cloudflare setiap 1 jam
        $schedule->command('sync:cloudflare-dns')->everyMinute();

        $schedule->command('check:dns-uptime')->everyMinute();
    }

    /**
     * Daftarkan perintah untuk aplikasi ini.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
