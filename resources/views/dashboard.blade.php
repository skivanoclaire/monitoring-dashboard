@extends('layouts.app')

@section('title', 'Monitoring Dashboard')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <h1 class="text-2xl font-bold">Monitoring VM: {{ $vmName }}</h1>

        <form method="GET" action="{{ route('dashboard') }}">
            <label for="vm" class="block text-sm font-medium text-gray-700">Pilih VM:</label>
            <select name="vm" id="vm" onchange="this.form.submit()" class="mt-1 block w-48 rounded-md border-gray-300 shadow-sm">
                @foreach($vms as $vm)
                    <option value="{{ $vm }}" {{ $vm === $vmName ? 'selected' : '' }}>{{ $vm }}</option>
                @endforeach
            </select>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-4 rounded-xl shadow-md">
                <h2 class="text-lg font-semibold mb-2">CPU Overview</h2>
                <div class="h-[300px]">
                    <canvas id="cpuChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-md">
                <h2 class="text-lg font-semibold mb-2">RAM Usage (%)</h2>
                <div class="h-[300px]">
                    <canvas id="ramChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-md">
                <h2 class="text-lg font-semibold mb-2">Disk Usage (%)</h2>
                <div class="h-[300px]">
                    <canvas id="diskChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-md">
                <h2 class="text-lg font-semibold mb-2">Bandwidth (Mbps)</h2>
                <div class="h-[300px]">
                    <canvas id="bandwidthChart"></canvas>
                </div>
            </div>
        </div>

        @php
            $latest = $logs->last();
            $cpu1 = $latest->cpu_1min ?? 0;
            $ram = $latest->ram_percent ?? 0;
            $disk = $latest->disk_percent ?? 0;
            $bw_down = $latest->bandwidth_down ?? 0;
            $bw_up = $latest->bandwidth_up ?? 0;

            function getStatus($value) {
                if ($value <= 50) return ['Normal', 'text-green-600'];
                elseif ($value <= 80) return ['Sedang', 'text-yellow-500'];
                else return ['Tinggi', 'text-red-600'];
            }

            [$cpuStatus, $cpuClass] = getStatus($cpu1);
            [$ramStatus, $ramClass] = getStatus($ram);
            [$diskStatus, $diskClass] = getStatus($disk);
        @endphp

        <div class="bg-white p-4 rounded-xl shadow-md mt-4">
            <h2 class="text-lg font-semibold mb-2">Status Beban Terkini</h2>
            <p>Data terakhir pada: <strong>{{ $latest->timestamp ?? '-' }}</strong></p>
            <ul class="list-disc ml-6 mt-2 space-y-1">
                <li>CPU Usage (1 min): <span class="{{ $cpuClass }}">{{ $cpu1 }}% ({{ $cpuStatus }})</span></li>
                <li>RAM Usage: <span class="{{ $ramClass }}">{{ $ram }}% ({{ $ramStatus }})</span></li>
                <li>Disk Usage: <span class="{{ $diskClass }}">{{ $disk }}% ({{ $diskStatus }})</span></li>
                <li>Bandwidth: ↓ {{ $bw_down }} Mbps | ↑ {{ $bw_up }} Mbps</li>
            </ul>
            <p class="mt-4 text-sm text-gray-500">* Normal: ≤ 50% &nbsp; | &nbsp; Sedang: 51–80% &nbsp; | &nbsp; Tinggi: > 80%</p>
        </div>
    </div>

    <script>
        const labels = {!! json_encode($logs->pluck('timestamp')) !!};
        const cpuUsage = {!! json_encode($logs->pluck('cpu_1min')) !!};
        const cpu5min = {!! json_encode($logs->pluck('cpu_5min')) !!};
        const cpu15min = {!! json_encode($logs->pluck('cpu_15min')) !!};
        const ramData = {!! json_encode($logs->pluck('ram_percent')) !!};
        const diskData = {!! json_encode($logs->pluck('disk_percent')) !!};
        const bwDownData = {!! json_encode($logs->pluck('bandwidth_down')) !!};
        const bwUpData = {!! json_encode($logs->pluck('bandwidth_up')) !!};

        const baseOptions = {
            responsive: true,
            maintainAspectRatio: false,
            elements: { point: { radius: 0 } },
            scales: {
                x: { display: false },
                y: { beginAtZero: true }
            }
        };

        new Chart(document.getElementById('cpuChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'CPU Usage Estimate (%)',
                        data: cpuUsage,
                        borderColor: '#3b82f6',
                        backgroundColor: '#3b82f6',
                        borderWidth: 1.5,
                        tension: 0.3
                    },
                    {
                        label: 'Load 1 min',
                        data: cpuUsage,
                        borderColor: '#10b981',
                        backgroundColor: '#10b981',
                        borderWidth: 1.5,
                        borderDash: [5, 5],
                        tension: 0.3
                    },
                    {
                        label: 'Load 5 min',
                        data: cpu5min,
                        borderColor: '#6366f1',
                        backgroundColor: '#6366f1',
                        borderWidth: 1.5,
                        borderDash: [5, 5],
                        tension: 0.3
                    },
                    {
                        label: 'Load 15 min',
                        data: cpu15min,
                        borderColor: '#f97316',
                        backgroundColor: '#f97316',
                        borderWidth: 1.5,
                        borderDash: [5, 5],
                        tension: 0.3
                    }
                ]
            },
            options: baseOptions
        });

        new Chart(document.getElementById('ramChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'RAM Usage (%)',
                    data: ramData,
                    borderColor: '#10b981',
                    backgroundColor: '#10b981',
                    borderWidth: 1.5,
                    tension: 0.3
                }]
            },
            options: baseOptions
        });

        new Chart(document.getElementById('diskChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Disk Usage (%)',
                    data: diskData,
                    borderColor: '#f59e0b',
                    backgroundColor: '#f59e0b',
                    borderWidth: 1.5,
                    tension: 0.3
                }]
            },
            options: baseOptions
        });

        new Chart(document.getElementById('bandwidthChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Download (↓ Mbps)',
                        data: bwDownData,
                        borderColor: '#6366f1',
                        backgroundColor: '#6366f1',
                        borderWidth: 1.5,
                        tension: 0.3
                    },
                    {
                        label: 'Upload (↑ Mbps)',
                        data: bwUpData,
                        borderColor: '#ec4899',
                        backgroundColor: '#ec4899',
                        borderWidth: 1.5,
                        tension: 0.3
                    }
                ]
            },
            options: baseOptions
        });
    </script>
@endsection
