@extends('layouts.app')

@section('title', 'Manajemen Plugin')
@section('page-title', 'Manajemen Plugin')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="relative rounded-3xl overflow-hidden bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 p-8 border border-slate-800/80 shadow-2xl">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_30%,rgba(99,102,241,0.08),transparent_50%)]"></div>
        <div class="relative z-10 max-w-3xl">
            <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl bg-gradient-to-r from-indigo-200 to-purple-200 bg-clip-text text-transparent">
                Ekstensi & Plugin Aplikasi
            </h1>
            <p class="mt-3 text-base text-slate-300 leading-relaxed">
                Kelola plugin modular yang aktif untuk tenant sekolah Anda. Mengaktifkan plugin akan mendaftarkan izin akses dan menu navigasi secara dinamis. Menonaktifkan plugin tidak akan menghapus data Anda.
            </p>
        </div>
    </div>

    <!-- Status Messages -->
    @if(session('status'))
        <div class="p-4 rounded-2xl bg-indigo-950/40 border border-indigo-800/60 text-indigo-300 flex items-center gap-3 shadow-lg shadow-indigo-950/20">
            <i class="fas fa-info-circle text-lg"></i>
            <span class="text-sm font-medium">{{ session('status') }}</span>
        </div>
    @endif

    <!-- Plugins Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($plugins as $p)
            @php
                $isActive = isset($activeMap[$p->id]) || $p->is_core;
            @endphp
            <div class="group relative rounded-2xl bg-slate-900/50 backdrop-blur-md border {{ $isActive ? 'border-indigo-500/30 bg-gradient-to-b from-slate-900/50 to-indigo-950/10' : 'border-slate-800/60' }} p-6 shadow-xl transition-all duration-300 hover:shadow-2xl hover:border-slate-700/60 flex flex-col justify-between overflow-hidden">
                @if($isActive)
                    <div class="absolute top-0 right-0 h-24 w-24 bg-gradient-to-bl from-indigo-500/10 to-transparent rounded-bl-full pointer-events-none"></div>
                @endif
                
                <div>
                    <!-- Header Card -->
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $isActive ? 'bg-gradient-to-br from-indigo-500 to-purple-600 shadow-indigo-500/20' : 'bg-slate-800 text-slate-400' }} text-white font-bold shadow-lg">
                                <i class="fas {{ $p->is_core ? 'fa-shield-halved' : 'fa-puzzle-piece' }} text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white group-hover:text-indigo-300 transition duration-200">{{ $p->nama }}</h3>
                                <span class="inline-flex items-center text-xs font-semibold text-slate-500">v{{ $p->versi }}</span>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div>
                            @if($p->is_core)
                                <span class="inline-flex items-center rounded-md bg-purple-400/10 px-2 py-1 text-xs font-medium text-purple-400 ring-1 ring-inset ring-purple-400/20">
                                    Inti Sistem
                                </span>
                            @elseif($isActive)
                                <span class="inline-flex items-center rounded-md bg-emerald-400/10 px-2 py-1 text-xs font-medium text-emerald-400 ring-1 ring-inset ring-emerald-400/20">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-slate-400/10 px-2 py-1 text-xs font-medium text-slate-400 ring-1 ring-inset ring-slate-400/20">
                                    Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Description -->
                    <p class="mt-4 text-sm text-slate-400 leading-relaxed min-h-[4rem]">
                        {{ $p->deskripsi ?? 'Tidak ada deskripsi untuk plugin ini.' }}
                    </p>
                </div>

                <!-- Action Button -->
                <div class="mt-6 pt-4 border-t border-slate-800/40 flex items-center justify-between">
                    <span class="text-xs text-slate-500">
                        @if($p->is_core)
                            Selalu aktif
                        @else
                            Aktivasi per tenant
                        @endif
                    </span>

                    <div>
                        @if($p->is_core)
                            <button class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800/80 px-3.5 py-2 text-xs font-semibold text-slate-400 cursor-not-allowed border border-slate-700/30" disabled>
                                <i class="fas fa-lock text-[10px]"></i> Permanen
                            </button>
                        @elseif($isActive)
                            <form method="POST" action="{{ route('plugins.deactivate', $p->kode) }}" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan plugin {{ $p->nama }}? Pengaturan & modul terkait akan disembunyikan.')">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-rose-950/20 px-3.5 py-2 text-xs font-semibold text-rose-400 border border-rose-900/30 hover:bg-rose-900/30 hover:text-rose-300 transition duration-200">
                                    <i class="fas fa-power-off text-[10px]"></i> Nonaktifkan
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('plugins.activate', $p->kode) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-md shadow-indigo-600/10 hover:bg-indigo-500 hover:shadow-indigo-500/20 transition duration-200">
                                    <i class="fas fa-play text-[10px]"></i> Aktifkan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
