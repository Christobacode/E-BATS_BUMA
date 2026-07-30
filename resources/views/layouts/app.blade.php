<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-BAST - IT Support BUMA IPR</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans flex h-screen overflow-hidden">

    <aside class="w-64 bg-[#001f3f] text-white flex flex-col shadow-xl">
        
        <div class="h-16 flex items-center justify-center border-b border-blue-900 bg-[#00152b] py-2 px-4 shadow-inner">
            <img src="{{ asset('img/logo-buma.png') }}" alt="Logo BUMA" class="h-10 object-contain drop-shadow-md">
            <span class="text-white font-bold text-lg ml-2 tracking-wider border-l border-gray-500 pl-2">IPR</span>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2">
            
            <a href="{{ route('peminjaman.dashboard') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('peminjaman.dashboard') ? 'bg-blue-600' : 'hover:bg-[#003366]' }} text-white rounded-md transition-colors">
                <i class="fas fa-home w-6 text-center"></i>
                <span class="ml-3 font-semibold text-sm">Beranda</span>
            </a>
            
            <a href="{{ route('peminjaman.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('peminjaman.index', 'peminjaman.create', 'peminjaman.return') ? 'bg-blue-600' : 'hover:bg-[#003366]' }} text-white rounded-md transition-colors">
                <i class="fas fa-file-signature w-6 text-center"></i>
                <span class="ml-3 font-semibold text-sm">Data BAST</span>
            </a>
            
            <a href="{{ route('peminjaman.riwayat') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('peminjaman.riwayat') ? 'bg-blue-600' : 'hover:bg-[#003366]' }} text-white rounded-md transition-colors">
                <i class="fas fa-history w-6 text-center"></i>
                <span class="ml-3 font-semibold text-sm">Riwayat</span>
            </a>
            <a href="{{ route('manage.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('manage.index') ? 'bg-blue-600' : 'hover:bg-[#003366]' }} text-white rounded-md transition-colors">
                <i class="fas fa-database w-6 text-center"></i>
                <span class="ml-3 font-semibold text-sm">Kelola data</span>
            </a>

        </nav>

        <div class="p-4 border-t border-blue-900 text-xs text-gray-400 text-center">
            &copy; {{ date('Y') }} IT Support Joshua<br>PT BUMA Site IPR
        </div>
    </aside>


    <main class="flex-1 flex flex-col h-screen overflow-y-auto">
        
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-8 z-10 shrink-0">
            <h2 class="text-xl font-bold text-gray-700">@yield('title', 'Sistem IT')</h2>
            
            <div class="flex items-center text-sm font-medium text-gray-600 bg-gray-100 px-4 py-2 rounded-full border">
                <i class="fas fa-user-circle text-lg mr-2 text-[#001f3f]"></i> Administrator IT
            </div>
        </header>

        <div class="p-8">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>