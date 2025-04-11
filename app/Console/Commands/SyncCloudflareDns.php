<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class SyncCloudflareDns extends Command
{
    protected $signature = 'sync:cloudflare-dns';
    protected $description = 'Sinkronisasi data DNS dari Cloudflare ke database';

    public function handle()
    {
        $zoneId = env('CLOUDFLARE_ZONE_ID');
        $apiToken = env('CLOUDFLARE_API_TOKEN');
        $baseUrl  = env('CLOUDFLARE_API_BASE', 'https://api.cloudflare.com/client/v4');

        $perPage = 100;
        $page = 1;
        $allRecords = [];

        do {
            $response = Http::withToken($apiToken)->get("{$baseUrl}/zones/{$zoneId}/dns_records", [
                'per_page' => $perPage,
                'page' => $page
            ]);

            if (!$response->ok()) {
                $this->error("Gagal ambil data halaman {$page}: " . $response->body());
                return;
            }

            $data = $response->json();

            $records = $data['result'] ?? [];
            $allRecords = array_merge($allRecords, $records);

            $page++;
            $totalPages = $data['result_info']['total_pages'] ?? 1;
        } while ($page <= $totalPages);

        // Hapus data lama
        DB::table('dns_records')->truncate();

        // Simpan data baru
        foreach ($allRecords as $record) {
            DB::table('dns_records')->insert([
                'subdomain'   => $record['name'],
                'type'        => $record['type'],
                'content'     => $record['content'],
                'ttl'         => $record['ttl'],
                'proxied'     => $record['proxied'] ?? false,
                'status'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $this->info("Berhasil sinkronisasi " . count($allRecords) . " record DNS.");
    }
}
