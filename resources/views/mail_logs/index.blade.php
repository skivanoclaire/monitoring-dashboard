@extends('layouts.app')

@section('title', 'Monitoring Email Keluar')

@section('content')
    <div class="bg-white shadow p-6 rounded mb-6">
        <h2 class="text-xl font-bold mb-4">Monitoring Email Keluar</h2>

        <!-- Filter Tanggal -->
        <form method="GET" class="flex items-center space-x-4 mb-6">
            <div>
                <label for="start_date" class="block text-sm text-gray-600">Dari Tanggal</label>
                <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="border border-gray-300 rounded px-3 py-1">
            </div>
            <div>
                <label for="end_date" class="block text-sm text-gray-600">Sampai Tanggal</label>
                <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="border border-gray-300 rounded px-3 py-1">
            </div>
            <div class="pt-5">
                <button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700">Filter</button>
            </div>
        </form>

        <!-- Tabel -->
        <div class="overflow-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 border-b text-left">Waktu</th>
                        <th class="py-2 px-4 border-b text-left">Pengirim</th>
                        <th class="py-2 px-4 border-b text-left">Penerima</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border-b">{{ $log->sent_at }}</td>
                            <td class="py-2 px-4 border-b">{{ $log->sender_email }}</td>
                            <td class="py-2 px-4 border-b">{{ $log->recipient_email }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>
@endsection
