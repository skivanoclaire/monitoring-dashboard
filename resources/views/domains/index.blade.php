@extends('layouts.app') {{-- Sesuaikan jika kamu pakai layout lain --}}

@section('content')
<div class="max-w-6xl mx-auto p-6">
    <h1 class="text-xl font-bold mb-4">Daftar Subdomain (Cloudflare)</h1>

    <table class="w-full table-auto border border-collapse border-gray-300 text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-1 py-2">No</th>
                <th class="border px-1 py-2">Subdomain</th>
                <th class="border px-1 py-2">Jenis Record</th>
                <th class="border px-1 py-2">TTL</th>
                <th class="border px-1 py-2">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $i => $record)
                <tr class="hover:bg-gray-50">
                    <td class="border px-1 py-2 text-center">{{ $i + 1 }}</td>
                    <td class="border px-1 py-2">{{ $record->subdomain }}</td>
                    <td class="border px-1 py-2 text-center">{{ $record->type }}</td>
                    <td class="border px-1 py-2 text-center">{{ $record->ttl }}</td>
                    <td class="border px-1 py-2 text-center">
@if($record->is_up)
    <span class="inline-block w-3 h-3 rounded-full bg-green-500" title="Online / Up"></span>
@else
    <span class="inline-block w-3 h-3 rounded-full bg-red-500" title="Offline / Down"></span>
@endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-3">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
