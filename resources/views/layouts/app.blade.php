<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Monitoring Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans">

    <!-- Navbar -->
    <nav class="bg-white shadow mb-6">
        <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
            <div class="text-lg font-bold text-blue-600">Monitoring Layanan DKISP-APTIKA</div>
            <ul class="flex space-x-6 text-sm font-medium text-gray-700">
                <li>
                    <a href="{{ route('dashboard') }}" class="hover:text-blue-600 {{ request()->is('dashboard*') ? 'text-blue-600' : '' }}">Virtual Machine</a>
                </li>
                <li>
                    <a href="{{ url('/domains') }}" class="hover:text-blue-600 {{ request()->is('domains*') ? 'text-blue-600' : '' }}">Domain & Subdomain</a>
                </li>
                <li>
                    <a href="https://cloud-as.ruijienetworks.com" class="hover:text-blue-600">Internet Perangkat Daerah</a>
                </li>
                <li>
                    <a href="" class="hover:text-blue-600">Email Resmi Pemerintah</a>
                </li>
                <!--
                <li>
                    <a href="" class="hover:text-blue-600">Pendaftaran Sistem Elektronik (PSE)</a>
                </li>
                 <li>
                    <a href="" class="hover:text-blue-600">Virtual Private Network (VPN)</a>
                </li>
                -->
            </ul>
        </div>
    </nav>

    <!-- Content -->
    <main class="max-w-7xl mx-auto px-4">
        @yield('content')
    </main>

</body>
</html>
