<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Odisha State Data Policy (OSDP) 2.0 • Capacity Building & MIS Monitoring Portal</title>
    <meta name="description" content="Statewide Capacity Building, Training Monitoring & Evidence Verification System under Odisha State Data Policy (OSDP) 2.0 across all 30 districts, 314 blocks, and Gram Panchayats.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Outfit', 'Inter', sans-serif; }
        .glow-ambient {
            background: radial-gradient(circle at 50% 0%, rgba(37, 99, 235, 0.08) 0%, rgba(248, 250, 252, 0) 70%);
        }
        .grid-pattern-light {
            background-size: 32px 32px;
            background-image: linear-gradient(to right, rgba(148, 163, 184, 0.12) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(148, 163, 184, 0.12) 1px, transparent 1px);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-blue-600 selection:text-white" x-data="{ mobileMenu: false }">

    <!-- 1. Government Official Top Strip -->
    <div class="bg-white border-b border-slate-200/90 text-slate-600 text-[11px] py-1.5 px-4 sm:px-8">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
            <!-- Left: Official Authority -->
            <div class="flex items-center space-x-2.5">
                <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-amber-100 text-amber-700 font-bold text-[9px]">🏛</span>
                <span class="font-bold text-slate-900 tracking-wide uppercase">Government of Odisha</span>
                <span class="text-slate-300">•</span>
                <span class="text-slate-600 font-medium">Electronics & Information Technology Department</span>
                <span class="text-slate-300 hidden md:inline">•</span>
                <span class="text-slate-500 hidden md:inline">Odisha Computer Application Centre (OCAC)</span>
            </div>

            <!-- Right: Standards & Contact -->
            <div class="flex items-center space-x-3 text-slate-500">
                <span class="inline-flex items-center space-x-1.5 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-medium text-[10px]">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>DPDP Act 2023 Compliant</span>
                </span>
                <span class="text-slate-300 hidden sm:inline">|</span>
                <span class="hidden sm:inline">Helpline: <a href="mailto:support.osdp@ocac.in" class="text-blue-600 hover:text-blue-800 font-mono font-medium transition">support.osdp@ocac.in</a></span>
            </div>
        </div>
    </div>

    <!-- 2. Sticky Glassmorphic Navbar (Matching Portal Theme) -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-white/95 border-b border-slate-200/90 shadow-2xs transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18">
                <!-- Brand / Logo -->
                <a href="{{ route('welcome') }}" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-md shadow-blue-600/20 group-hover:scale-105 transition duration-200">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="font-display font-extrabold text-lg text-slate-900 tracking-tight leading-none">OSDP 2.0</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-orange-50 text-orange-600 border border-orange-200 uppercase tracking-wider leading-none">
                                MIS Portal
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 font-medium pt-0.5">Odisha State Data Policy • Capacity Building & Governance</p>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden lg:flex items-center space-x-8 text-xs font-semibold text-slate-600">
                    <a href="#about" class="hover:text-blue-600 transition">About Policy</a>
                    <a href="#architecture" class="hover:text-blue-600 transition">4-Tier Framework</a>
                    <a href="#metrics" class="hover:text-blue-600 transition">Statewide Reach</a>
                    <a href="#faq" class="hover:text-blue-600 transition">FAQs</a>
                </nav>

                <!-- Action CTA -->
                <div class="hidden sm:flex items-center space-x-3">
                    @auth
                        <div class="flex items-center space-x-3 pl-3 border-l border-slate-200">
                            <a href="{{ route('dashboard') }}"
                               class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold text-xs shadow-md shadow-blue-600/20 transition flex items-center space-x-2">
                                <span>Dashboard</span>
                            </a>
                        </div>
                    @else
                        <a href="{{ route('login') }}"
                           class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-600/20 transition flex items-center space-x-2 hover:shadow-lg hover:shadow-blue-600/30">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span>Sign In</span>
                        </a>
                    @endauth
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="lg:hidden flex items-center">
                    <button type="button" @click="mobileMenu = !mobileMenu"
                            class="p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                            <path x-show="mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display: none;" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div x-show="mobileMenu"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="lg:hidden bg-white border-b border-slate-200 px-5 pt-3 pb-6 space-y-3 shadow-lg"
             style="display: none;">
            <a href="#about" @click="mobileMenu = false" class="block py-2 text-sm font-semibold text-slate-700 hover:text-blue-600">About Policy</a>
            <a href="#architecture" @click="mobileMenu = false" class="block py-2 text-sm font-semibold text-slate-700 hover:text-blue-600">4-Tier Framework</a>
            <a href="#metrics" @click="mobileMenu = false" class="block py-2 text-sm font-semibold text-slate-700 hover:text-blue-600">Statewide Reach</a>
            <a href="#faq" @click="mobileMenu = false" class="block py-2 text-sm font-semibold text-slate-700 hover:text-blue-600">FAQs</a>
            <div class="pt-3 border-t border-slate-200">
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs text-center block shadow-sm">
                         Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs text-center block shadow-sm">
                     Sign In
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- 3. Hero Section (Clean Light Theme with Government Blue Accents) -->
    <section class="relative overflow-hidden pt-12 pb-20 md:py-20 bg-gradient-to-b from-blue-50/60 via-white to-slate-50 border-b border-slate-200/90 grid-pattern-light glow-ambient">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-4xl mx-auto space-y-6">

                <!-- Floating State Tagline -->
                <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                    <span class="text-xs font-semibold text-slate-700">Statewide Capacity Building & Verification Platform</span>
                    <span class="text-slate-300">•</span>
                    <span class="text-xs font-bold text-orange-600 uppercase tracking-wider">Odisha State Data Policy 2.0</span>
                </div>

                <!-- Main Hero Headline -->
                <h1 class="font-display text-3xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-tight sm:leading-tight">
                    Institutionalizing Data Excellence Across
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-800">
                        Odisha Governance
                    </span>
                </h1>

                <!-- Subtitle -->
                <p class="text-sm sm:text-base lg:text-lg text-slate-600 max-w-3xl mx-auto leading-relaxed font-normal">
                    The official Monitoring Information System for the execution of <strong class="text-slate-900 font-semibold">Odisha State Data Policy (OSDP) 2.0</strong>. Empowering <span class="text-blue-700 font-bold">{{ number_format($targetParticipants) }}+</span> government officials across all 30 Districts, 314 Blocks, and Gram Panchayats with secure, transparent, and compliant data stewardship.
                </p>


                <!-- Compliance Pills -->
                <div class="pt-4 flex flex-wrap items-center justify-center gap-3 text-xs text-slate-600">
                    <span class="inline-flex items-center space-x-1.5 bg-white px-3 py-1 rounded-full border border-slate-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="font-medium">48h SLA Review Cycle</span>
                    </span>
                    <span class="inline-flex items-center space-x-1.5 bg-white px-3 py-1 rounded-full border border-slate-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span class="font-medium">Tamper-Proof Evidence Vault</span>
                    </span>
                    <span class="inline-flex items-center space-x-1.5 bg-white px-3 py-1 rounded-full border border-slate-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <span class="font-medium">Form 2 Signature Transcripts</span>
                    </span>
                </div>
            </div>

            <!-- Real-Time Telemetry Counters Grid -->
            <div class="mt-14 max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- 1. Districts -->
                <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-blue-400 hover:shadow-md transition group">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Coverage Scope</span>
                    <div class="flex items-baseline space-x-1">
                        <span class="font-display text-3xl sm:text-4xl font-extrabold text-blue-600 group-hover:text-blue-700 transition">{{ $totalDistricts }}</span>
                        <span class="text-xs font-semibold text-slate-500">Districts</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">100% geographic reach across all collectorates</p>
                </div>

                <!-- 2. Blocks -->
                <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-indigo-400 hover:shadow-md transition group">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Administrative Blocks</span>
                    <div class="flex items-baseline space-x-1">
                        <span class="font-display text-3xl sm:text-4xl font-extrabold text-indigo-600 group-hover:text-indigo-700 transition">{{ $totalBlocks }}</span>
                        <span class="text-xs font-semibold text-slate-500">Blocks</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">Sub-divisional and block line departments</p>
                </div>

                <!-- 3. Batches -->
                <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-purple-400 hover:shadow-md transition group">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Scheduled Sessions</span>
                    <div class="flex items-baseline space-x-1">
                        <span class="font-display text-3xl sm:text-4xl font-extrabold text-purple-600 group-hover:text-purple-700 transition">{{ number_format($totalBatches) }}</span>
                        <span class="text-xs font-semibold text-slate-500">/ {{ number_format($targetBatches) }}</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">Master calendar batches tracked via Form 2</p>
                </div>

                <!-- 4. Officials -->
                <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-emerald-400 hover:shadow-md transition group">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Target Personnel</span>
                    <div class="flex items-baseline space-x-1">
                        <span class="font-display text-3xl sm:text-4xl font-extrabold text-emerald-600 group-hover:text-emerald-700 transition">{{ number_format($targetParticipants) }}</span>
                        <span class="text-xs font-semibold text-slate-500">Officials</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">Statewide public officers & grassroots cadre</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Strategic Pillars of OSDP 2.0 (Meaningful Core) -->
    <section id="about" class="py-20 bg-slate-50/70 border-b border-slate-200/90">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-3">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 uppercase tracking-wider">
                    Policy Foundations
                </span>
                <h2 class="font-display text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Four Strategic Pillars of Data Policy 2.0
                </h2>
                <p class="text-xs sm:text-sm text-slate-600">
                    ODISHA STATE DATA POLICY 2.0 establishes an institutional mandate for data valuation, citizen privacy, and inter-departmental trust.
                </p>
            </div>

            <div class="mt-14 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Pillar 1: Classification -->
                <div class="p-6 rounded-2xl bg-white border border-slate-200 hover:border-blue-300 hover:shadow-md transition-all space-y-3.5 shadow-2xs group">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center group-hover:scale-105 transition">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-base text-slate-900">1. Data Classification</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Rigorous categorisation of government data into <strong class="text-slate-900">Open</strong>, <strong class="text-slate-900">Shareable with Conditions</strong>, and <strong class="text-slate-900">Restricted</strong> with standardized metadata dictionaries.
                    </p>
                    <div class="text-[11px] text-blue-600 font-semibold flex items-center space-x-1 pt-1">
                        <span>Standardized Catalogs</span>
                        <span>→</span>
                    </div>
                </div>

                <!-- Pillar 2: DPDP Privacy -->
                <div class="p-6 rounded-2xl bg-white border border-slate-200 hover:border-emerald-300 hover:shadow-md transition-all space-y-3.5 shadow-2xs group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center group-hover:scale-105 transition">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-base text-slate-900">2. DPDP Act 2023 & Privacy</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Full compliance with India's Digital Personal Data Protection Act 2023. Embedding consent governance, data minimization, anonymization, and security safeguards.
                    </p>
                    <div class="text-[11px] text-emerald-600 font-semibold flex items-center space-x-1 pt-1">
                        <span>Statutory Protection</span>
                        <span>→</span>
                    </div>
                </div>

                <!-- Pillar 3: Interoperability -->
                <div class="p-6 rounded-2xl bg-white border border-slate-200 hover:border-indigo-300 hover:shadow-md transition-all space-y-3.5 shadow-2xs group">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center group-hover:scale-105 transition">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-base text-slate-900">3. API & Interoperability</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Demolishing departmental silos through automated, secure RESTful API exchanges, unified master registries, and common identifier integration.
                    </p>
                    <div class="text-[11px] text-indigo-600 font-semibold flex items-center space-x-1 pt-1">
                        <span>Unified Data Exchange</span>
                        <span>→</span>
                    </div>
                </div>

                <!-- Pillar 4: Grassroots Evidence -->
                <div class="p-6 rounded-2xl bg-white border border-slate-200 hover:border-amber-300 hover:shadow-md transition-all space-y-3.5 shadow-2xs group">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center group-hover:scale-105 transition">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-base text-slate-900">4. Evidence Governance</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        A rigorous multi-point verification model backed by geo-tagged media and two-stage review by District Coordinators and State PMU before settlement.
                    </p>
                    <div class="text-[11px] text-amber-600 font-semibold flex items-center space-x-1 pt-1">
                        <span>Strict Audit Trails</span>
                        <span>→</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. The 4-Tier Statewide Capacity Building Architecture -->
    <section id="architecture" class="py-20 bg-white border-b border-slate-200/90">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-3">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase tracking-wider">
                    Institutional Hierarchy
                </span>
                <h2 class="font-display text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    4-Tier Capacity Building Architecture
                </h2>
                <p class="text-xs sm:text-sm text-slate-600">
                    Structured learning pathways customized for every echelon of Odisha's administrative machinery.
                </p>
            </div>

            <div class="mt-14 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Tier 1 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col justify-between space-y-4 hover:border-slate-300 hover:shadow-sm transition">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                Tier 1 • State Level
                            </span>
                            <span class="text-xs text-slate-400 font-bold">Level 1</span>
                        </div>
                        <h4 class="font-bold text-base text-slate-900">Secretariat & Department Heads</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Principal Secretaries, Special Secretaries, HoDs, and State Chief Data Officers (CDOs).
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-200 text-[11px] text-slate-600 space-y-1">
                        <div><strong class="text-slate-800">Focus:</strong> Policy vision, inter-departmental data treaties & state frameworks.</div>
                        <div><strong class="text-slate-800">Audience:</strong> Top Leadership</div>
                    </div>
                </div>

                <!-- Tier 2 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col justify-between space-y-4 hover:border-slate-300 hover:shadow-sm transition">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                Tier 2 • District Level
                            </span>
                            <span class="text-xs text-slate-400 font-bold">Level 2</span>
                        </div>
                        <h4 class="font-bold text-base text-slate-900">District Collectors & Nodal Officers</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            District Collectors, ADMs, District Data Management Units (DDMUs), and Line Department District Heads.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-200 text-[11px] text-slate-600 space-y-1">
                        <div><strong class="text-slate-800">Focus:</strong> District data governance, resource planning, and compliance monitoring.</div>
                        <div><strong class="text-slate-800">Audience:</strong> 30 Districts</div>
                    </div>
                </div>

                <!-- Tier 3 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col justify-between space-y-4 hover:border-slate-300 hover:shadow-sm transition">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                Tier 3 • Block Level
                            </span>
                            <span class="text-xs text-slate-400 font-bold">Level 3</span>
                        </div>
                        <h4 class="font-bold text-base text-slate-900">Block Field Machinery</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Block Development Officers (BDOs), Tahsildars, CDPOs, Block Extension Officers, and Technical Staff.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-200 text-[11px] text-slate-600 space-y-1">
                        <div><strong class="text-slate-800">Focus:</strong> Workflow execution, operational data registers, and field validation.</div>
                        <div><strong class="text-slate-800">Audience:</strong> 314 Blocks</div>
                    </div>
                </div>

                <!-- Tier 4 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col justify-between space-y-4 hover:border-slate-300 hover:shadow-sm transition">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Tier 4 • Gram Panchayat
                            </span>
                            <span class="text-xs text-slate-400 font-bold">Level 4</span>
                        </div>
                        <h4 class="font-bold text-base text-slate-900">Grassroots Cadres & PEOs</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Panchayat Executive Officers (PEOs), Gram Rozgar Sevaks (GRS), Sarpanches, and Ward Members.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-200 text-[11px] text-slate-600 space-y-1">
                        <div><strong class="text-slate-800">Focus:</strong> Citizen data collection, Form 2 registers, and grievance data integrity.</div>
                        <div><strong class="text-slate-800">Audience:</strong> 6,798+ GPs</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. Statewide Reach & Live Sessions Showcase -->
    <section id="metrics" class="py-20 bg-slate-50/70 border-b border-slate-200/90">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
                <div class="space-y-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 uppercase tracking-wider">
                        Live Roster
                    </span>
                    <h2 class="font-display text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Real-Time Statewide Deployment
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600">
                        Live sessions underway across Odisha's Collectorates and Administrative Blocks.
                    </p>
                </div>
                <div class="text-xs text-slate-600">
                    <span class="font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">{{ $scheduledBatches }} Batches in Active Pipeline</span>
                </div>
            </div>

            <!-- Live Batches Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                @forelse($recentBatches as $batch)
                <div class="p-5 rounded-2xl bg-white border border-slate-200 space-y-3 shadow-2xs hover:border-blue-300 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold text-blue-600">{{ $batch->batch_code }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                            {{ $batch->status === 'verified' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($batch->status === 'scheduled' ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                            {{ str_replace('_', ' ', $batch->status) }}
                        </span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block">{{ $batch->tier?->name ?? 'District Level' }}</span>
                        <h4 class="font-bold text-sm text-slate-900">{{ $batch->district?->name ?? 'State Level' }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5 truncate">{{ $batch->venue?->name ?? 'Collectorate Conference Hall' }}</p>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                        <span>{{ $batch->scheduled_date->format('d M Y') }}</span>
                        <span class="font-semibold text-slate-700">{{ $batch->present_participants }} Present</span>
                    </div>
                </div>
                @empty
                <div class="col-span-4 p-8 text-center text-slate-500 bg-white rounded-2xl border border-slate-200">
                    Master calendar sessions scheduled across all 30 districts are synchronized live into the portal.
                </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- 7. Frequently Asked Questions (Accordion) -->
    <section id="faq" class="py-20 bg-white border-b border-slate-200/90" x-data="{ openFaq: null }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center space-y-3">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase tracking-wider">
                    Officer Inquiries
                </span>
                <h2 class="font-display text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Frequently Asked Questions
                </h2>
                <p class="text-xs sm:text-sm text-slate-600">
                    Key information regarding policy implementation, role requirements, and technical access.
                </p>
            </div>

            <div class="space-y-3 text-xs sm:text-sm">
                <!-- FAQ 1 -->
                <div class="rounded-2xl bg-slate-50 border border-slate-200 overflow-hidden hover:border-slate-300 transition">
                    <button type="button" @click="openFaq = openFaq === 1 ? null : 1"
                            class="w-full p-5 text-left font-bold text-slate-900 flex items-center justify-between hover:text-blue-600 transition">
                        <span>What is the primary mandate of Odisha State Data Policy (OSDP) 2.0?</span>
                        <svg class="w-4 h-4 transform transition-transform text-slate-500" :class="openFaq === 1 ? 'rotate-180 text-blue-600' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="openFaq === 1" x-collapse class="px-5 pb-5 text-slate-600 leading-relaxed text-xs sm:text-sm">
                        OSDP 2.0 establishes an institutional architecture for government data governance, open public data sharing, cross-departmental API interoperability, and stringent compliance with India's Digital Personal Data Protection (DPDP) Act 2023 across all 30 districts of Odisha.
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="rounded-2xl bg-slate-50 border border-slate-200 overflow-hidden hover:border-slate-300 transition">
                    <button type="button" @click="openFaq = openFaq === 2 ? null : 2"
                            class="w-full p-5 text-left font-bold text-slate-900 flex items-center justify-between hover:text-blue-600 transition">
                        <span>Who is mandated to participate in the capacity building programme?</span>
                        <svg class="w-4 h-4 transform transition-transform text-slate-500" :class="openFaq === 2 ? 'rotate-180 text-blue-600' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="openFaq === 2" x-collapse class="px-5 pb-5 text-slate-600 leading-relaxed text-xs sm:text-sm">
                        The programme covers 4 administrative tiers: State Secretariat & Department Heads (Tier 1), District Collectors & Nodal Officers (Tier 2), Block Development Officers & Tahsildars (Tier 3), and Panchayat Executive Officers (PEOs) & Gram Rozgar Sevaks (Tier 4).
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="rounded-2xl bg-slate-50 border border-slate-200 overflow-hidden hover:border-slate-300 transition">
                    <button type="button" @click="openFaq = openFaq === 3 ? null : 3"
                            class="w-full p-5 text-left font-bold text-slate-900 flex items-center justify-between hover:text-blue-600 transition">
                        <span>How is attendance and training delivery verified?</span>
                        <svg class="w-4 h-4 transform transition-transform text-slate-500" :class="openFaq === 3 ? 'rotate-180 text-blue-600' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="openFaq === 3" x-collapse class="px-5 pb-5 text-slate-600 leading-relaxed text-xs sm:text-sm">
                        Every batch requires Form 2 manual physical signatures (morning and post-lunch), geo-tagged group photo with the official approved banner, classroom teaching photo, 1-minute video clip, participant feedback bundle, and batch completion report uploaded within 48 hours.
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="rounded-2xl bg-slate-50 border border-slate-200 overflow-hidden hover:border-slate-300 transition">
                    <button type="button" @click="openFaq = openFaq === 4 ? null : 4"
                            class="w-full p-5 text-left font-bold text-slate-900 flex items-center justify-between hover:text-blue-600 transition">
                        <span>How can an officer get credentials to access the MIS portal?</span>
                        <svg class="w-4 h-4 transform transition-transform text-slate-500" :class="openFaq === 4 ? 'rotate-180 text-blue-600' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="openFaq === 4" x-collapse class="px-5 pb-5 text-slate-600 leading-relaxed text-xs sm:text-sm">
                        Credentials are automatically provisioned and mapped to authorized government email IDs by the State Super Administrator and OCAC Nodal Officers based on district and role assignments.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. Official Government Footer (Light & Matching Theme) -->
    <footer class="bg-white border-t border-slate-200 text-slate-600 text-xs py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Col 1: Brand & Authority -->
                <div class="md:col-span-2 space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-blue-500/20">
                            OD
                        </div>
                        <div>
                            <span class="font-display font-extrabold text-base text-slate-900 block leading-tight">OSDP 2.0 MIS Portal</span>
                            <span class="text-[11px] text-slate-500 font-medium">Odisha State Data Policy Management Information System</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed max-w-md pt-2">
                        Designed, developed, and maintained by the Odisha Computer Application Centre (OCAC), designated State Nodal Agency under the Electronics & Information Technology Department, Government of Odisha.
                    </p>
                    <p class="text-[11px] text-slate-500 font-mono">
                        RFP Tender Reference: OCAC/OSDP/2026/01 • DPDP Compliance Verified
                    </p>
                </div>

                <!-- Col 2: Navigation Links -->
                <div class="space-y-3">
                    <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Quick Navigation</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="#about" class="text-slate-600 hover:text-blue-600 transition">About Policy 2.0</a></li>
                        <li><a href="#architecture" class="text-slate-600 hover:text-blue-600 transition">4-Tier Governance Hierarchy</a></li>
                        <li><a href="#metrics" class="text-slate-600 hover:text-blue-600 transition">Statewide Reach</a></li>
                        <li><a href="#faq" class="text-slate-600 hover:text-blue-600 transition">Frequently Asked Questions</a></li>
                    </ul>
                </div>

                <!-- Col 3: Contact & Support -->
                <div class="space-y-3">
                    <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Nodal Helpdesk</h4>
                    <div class="space-y-1.5 text-xs text-slate-600">
                        <p class="text-slate-800 font-semibold">Odisha Computer Application Centre (OCAC)</p>
                        <p>OCAC Building, Acharya Vihar</p>
                        <p>Bhubaneswar - 751013, Odisha</p>
                        <p class="pt-1">Email: <a href="mailto:support.osdp@ocac.in" class="text-blue-600 hover:underline font-medium">support.osdp@ocac.in</a></p>
                        <p>Helpdesk: <span class="text-slate-800 font-mono font-medium">0674-2588280 / 2588282</span></p>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright & Security Disclaimer -->
            <div class="pt-8 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-slate-500">
                <p>© {{ date('Y') }} Government of Odisha. All Rights Reserved. Hosted at State Data Centre (SDC).</p>
                <div class="flex items-center space-x-4">
                    <span>Privacy Policy</span>
                    <span>•</span>
                    <span>Terms of Use</span>
                    <span>•</span>
                    <span class="text-emerald-600 font-medium">100% Encrypted & Audited</span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
