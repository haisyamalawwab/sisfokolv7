<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SISFOKOL')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Tailwind CSS (via CDN fallback to ensure it works instantly, plus Vite) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Modern Breeze/Livewire Style CSS Bridge for Bootstrap/AdminLTE classes */
        .row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .col-md-12 {
            grid-column: 1 / -1;
        }
        .card {
            background-color: rgba(15, 23, 42, 0.45); /* bg-slate-900/45 */
            border: 1px solid rgba(51, 65, 85, 0.3); /* border-slate-700/30 */
            backdrop-filter: blur(16px);
            border-radius: 1rem;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(51, 65, 85, 0.2);
            background-color: rgba(15, 23, 42, 0.3);
        }
        .card-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #f8fafc;
            margin: 0;
        }
        .card-body {
            padding: 1.5rem;
        }
        .info-box {
            display: flex;
            align-items: center;
            background-color: rgba(15, 23, 42, 0.45);
            border: 1px solid rgba(51, 65, 85, 0.3);
            backdrop-filter: blur(16px);
            border-radius: 1rem;
            padding: 1.25rem;
            gap: 1.25rem;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }
        .info-box-icon {
            display: flex;
            height: 3.5rem;
            width: 3.5rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
            font-size: 1.35rem;
            color: #ffffff;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .info-box-content {
            display: flex;
            flex-direction: column;
        }
        .info-box-text {
            font-size: 0.75rem;
            font-weight: 500;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .info-box-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #f8fafc;
            margin-top: 0.15rem;
        }
        .bg-primary { background-image: linear-gradient(135deg, #6366f1, #4f46e5); } /* Indigo Gradient */
        .bg-success { background-image: linear-gradient(135deg, #10b981, #059669); } /* Emerald Gradient */
        .bg-warning { background-image: linear-gradient(135deg, #f59e0b, #d97706); } /* Amber Gradient */
        .bg-info { background-image: linear-gradient(135deg, #14b8a6, #0d9488); } /* Teal Gradient */

        .table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.875rem;
        }
        .table th {
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: #94a3b8;
            border-bottom: 1px solid rgba(51, 65, 85, 0.3);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        .table td {
            padding: 1rem 1.5rem;
            color: #cbd5e1;
            border-bottom: 1px solid rgba(51, 65, 85, 0.15);
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(30, 41, 59, 0.15);
        }
        .table-striped tbody tr:hover {
            background-color: rgba(30, 41, 59, 0.3);
            transition: background-color 0.2s ease;
        }
    </style>
</head>
<body class="h-full text-slate-100 antialiased" x-data="{ sidebarOpen: false }">

    <!-- Impersonation Banner -->
    @include('partials.impersonation_banner')

    <div class="flex min-h-full">
        <!-- Sidebar for Desktop -->
        <aside class="hidden lg:flex lg:flex-col lg:w-72 lg:fixed lg:inset-y-0 lg:z-50 bg-slate-900 border-r border-slate-800/60">
            <div class="flex flex-col flex-1 min-h-0">
                <!-- Logo -->
                <div class="flex items-center h-20 px-6 border-b border-slate-800/60 bg-slate-950/40">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-content-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-md shadow-indigo-500/20 text-white font-bold text-lg justify-center">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <span class="text-base font-bold bg-gradient-to-r from-indigo-200 to-purple-200 bg-clip-text text-transparent">SISFOKOL v7</span>
                            <p class="text-xs text-slate-500">Modular Monolith</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                    @include('layouts.partials.menu')
                </nav>

                <!-- Footer / School Info -->
                <div class="p-4 border-t border-slate-800/60 bg-slate-950/20">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-lg bg-indigo-950 flex items-center justify-center text-indigo-400 font-semibold text-xs border border-indigo-900/50">
                            KM
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-medium text-slate-300 truncate">{{ auth()->user()?->nama }}</p>
                            <p class="text-[10px] text-slate-500 truncate">{{ auth()->user()?->email }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile Sidebar Off-canvas -->
        <div class="relative z-50 lg:hidden" x-show="sidebarOpen" x-description="Off-canvas menu for mobile" role="dialog" aria-modal="true" x-cloak>
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="sidebarOpen = false" x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

            <div class="fixed inset-0 flex">
                <div class="relative flex flex-col flex-1 w-full max-w-xs bg-slate-900 border-r border-slate-800" x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
                    <div class="absolute top-0 right-0 flex justify-center w-16 pt-4 -mr-16">
                        <button type="button" class="flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500" @click="sidebarOpen = false">
                            <span class="sr-only">Close sidebar</span>
                            <i class="fas fa-times text-white text-lg"></i>
                        </button>
                    </div>

                    <div class="flex items-center h-20 px-6 border-b border-slate-800 bg-slate-950/40">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold text-lg">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <span class="text-base font-bold bg-gradient-to-r from-indigo-200 to-purple-200 bg-clip-text text-transparent">SISFOKOL v7</span>
                        </div>
                    </div>

                    <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                        @include('layouts.partials.menu')
                    </nav>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 lg:pl-72 min-h-screen bg-slate-950">
            <!-- Top Navbar -->
            <header class="flex items-center justify-between h-20 px-4 sm:px-6 lg:px-8 border-b border-slate-800 bg-slate-900/50 backdrop-blur-md sticky top-0 z-40">
                <!-- Mobile menu button -->
                <button type="button" class="lg:hidden p-2 text-slate-400 hover:text-slate-200 focus:outline-none" @click="sidebarOpen = true">
                    <span class="sr-only">Open sidebar</span>
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <!-- Page Title / Search -->
                <div class="flex-1 hidden md:flex items-center pl-4 lg:pl-0">
                    <h2 class="text-lg font-semibold text-slate-100">@yield('page-title', 'Dashboard')</h2>
                </div>

                <!-- User Actions -->
                <div class="flex items-center gap-4 ml-auto">
                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" class="flex items-center gap-2.5 p-1.5 rounded-full hover:bg-slate-800/80 transition focus:outline-none" @click="open = !open" @click.away="open = false">
                            <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-sm border border-indigo-400/20">
                                {{ substr(auth()->user()?->nama ?? 'U', 0, 1) }}
                            </div>
                            <span class="hidden md:flex items-center text-sm font-medium text-slate-300 pr-2">
                                {{ auth()->user()?->nama }}
                                <i class="fas fa-chevron-down ml-2 text-xs text-slate-500"></i>
                            </span>
                        </button>

                        <div class="absolute right-0 mt-2.5 w-56 origin-top-right rounded-2xl bg-slate-900 border border-slate-800 p-2 shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none" x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" x-cloak>
                            <a href="{{ route('password.change') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm text-slate-300 hover:bg-slate-800 hover:text-white transition">
                                <i class="fas fa-key text-slate-500 w-5"></i> Ganti Password
                            </a>
                            <hr class="border-slate-800 my-1">
                            <form action="{{ route('logout') }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm text-rose-400 hover:bg-rose-950/20 hover:text-rose-300 transition text-left">
                                    <i class="fas fa-sign-out-alt w-5"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 py-10 px-4 sm:px-6 lg:px-8">
                @if (session('success'))
                    <div class="mb-6 p-4 rounded-2xl bg-emerald-950/40 border border-emerald-800/60 text-emerald-300 flex items-center gap-3">
                        <i class="fas fa-check-circle text-lg"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-6 p-4 rounded-2xl bg-rose-950/40 border border-rose-800/60 text-rose-300 flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-lg"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="py-6 border-t border-slate-900 bg-slate-950 text-center text-xs text-slate-600">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Built with Laravel 11 & Tailwind CSS.
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
