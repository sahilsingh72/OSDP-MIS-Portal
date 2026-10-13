<!DOCTYPE html>
<html lang="en" class="min-h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'OSDP MIS Portal' }} - Odisha State Data Policy 2.0</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex flex-col py-6 sm:py-10 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-slate-100 via-slate-50 to-blue-50/70 text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

    <div class="my-auto w-full max-w-md mx-auto">
        <div class="text-center">
            <!-- Odisha Government Insignia & Brand -->
            <div class="inline-flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 shadow-xl shadow-blue-600/20 mb-3 sm:mb-4 border border-blue-400/30 text-white">
                <svg class="w-8 h-8 sm:w-9 sm:h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                OSDP MIS PORTAL
            </h1>
            <p class="mt-1 text-xs sm:text-sm font-semibold text-orange-600 uppercase tracking-wider">
                Odisha State Data Policy 2.0
            </p>
            <p class="text-xs text-slate-500 mt-0.5">
                Capacity Building, Training & Evidence Management System
            </p>
        </div>

        <div class="mt-6 sm:mt-8">
            <div class="bg-white/95 backdrop-blur-xl py-6 sm:py-8 px-5 sm:px-8 shadow-xl shadow-slate-200/70 rounded-2xl border border-slate-200">
                @if (session('status'))
                    <div class="mb-5 rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 flex items-start space-x-3">
                        <svg class="w-5 h-5 flex-shrink-0 text-emerald-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <div>{!! session('status') !!}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800">
                        <div class="font-semibold mb-1 flex items-center space-x-2">
                            <svg class="w-4 h-4 flex-shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Please correct the errors below:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs text-rose-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>

            <!-- Footer Notice -->
            <div class="mt-6 sm:mt-8 text-center text-xs text-slate-500 space-y-1">
                <p class="font-medium text-slate-600">Odisha Computer Application Centre (OCAC), Government of Odisha</p>
                <p class="text-[11px] text-slate-400">Compliant with Digital Personal Data Protection (DPDP) Act 2023 & OSDC Standards</p>
            </div>
        </div>
    </div>

</body>
</html>
