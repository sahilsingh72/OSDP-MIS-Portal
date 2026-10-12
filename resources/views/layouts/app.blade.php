<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50 text-slate-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - OSDP MIS Portal</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex antialiased bg-slate-100" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-40 lg:hidden"
        @click="sidebarOpen = false"
        style="display: none;"></div>

    <!-- Sidebar Navigation -->
    @include('components.sidebar')

    <!-- Main Content Layout -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden lg:pl-64">

        <!-- Top Header / Navbar -->
        @include('components.navbar')

        <!-- Main Body Content -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            <!-- Flash Message Banner -->
            @include('components.flash-message')

            <!-- Yield Content -->
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
