@extends('layouts.app')

@section('title', 'Laporan Presensi — SISFOKOL')
@section('page-title', '📈 Laporan Presensi Bulanan')

@push('styles')
<style>
    .stat-card {
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgb(30, 41, 59);
        border-radius: 1.5rem;
        backdrop-filter: blur(8px);
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--stat-accent);
    }
    .chart-bar {
        transition: height 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- ─── Month Filter ─── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-100">Laporan Presensi</h1>
            <p class="text-sm text-slate-500 mt-0.5">Rekapitulasi data kehadiran {{ $startDate->format('F Y') }}</p>
        </div>
        <form method="GET" action="{{ route('presence.laporan') }}" class="flex items-center gap-3">
            <input type="month" name="month" value="{{ $month }}"
                class="px-4 py-2.5 rounded-2xl bg-slate-800 border border-slate-700 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
            <button type="submit"
                class="px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition flex items-center gap-2">
                <i class="fas fa-filter"></i> Tampilkan
            </button>
        </form>
    </div>

    {{-- ─── Stats Grid ─── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card" style="--stat-accent: linear-gradient(90deg, #6366f1, #8b5cf6)">
            <div class="flex items-center justify-between mb-3">
                <div class="h-10 w-10 rounded-xl bg-indigo-950/70 flex items-center justify-center text-indigo-400">
                    <i class="fas fa-user-check"></i>
                </div>
                <span class="text-xs text-slate-500">Total Hadir</span>
            </div>
            <p class="text-3xl font-bold text-slate-100">{{ number_format($totalPresensi) }}</p>
            <p class="text-xs text-slate-500 mt-1">scan masuk bulan ini</p>
        </div>

        <div class="stat-card" style="--stat-accent: linear-gradient(90deg, #f59e0b, #ef4444)">
            <div class="flex items-center justify-between mb-3">
                <div class="h-10 w-10 rounded-xl bg-amber-950/70 flex items-center justify-center text-amber-400">
                    <i class="fas fa-clock"></i>
                </div>
                <span class="text-xs text-slate-500">Terlambat</span>
            </div>
            <p class="text-3xl font-bold text-amber-300">{{ number_format($totalTerlambat) }}</p>
            @if($totalPresensi > 0)
            <p class="text-xs text-slate-500 mt-1">{{ round($totalTerlambat / $totalPresensi * 100, 1) }}% dari total hadir</p>
            @else
            <p class="text-xs text-slate-500 mt-1">—</p>
            @endif
        </div>

        <div class="stat-card" style="--stat-accent: linear-gradient(90deg, #ef4444, #dc2626)">
            <div class="flex items-center justify-between mb-3">
                <div class="h-10 w-10 rounded-xl bg-rose-950/70 flex items-center justify-center text-rose-400">
                    <i class="fas fa-user-times"></i>
                </div>
                <span class="text-xs text-slate-500">Alpa</span>
            </div>
            <p class="text-3xl font-bold text-rose-300">{{ number_format($totalAbsen) }}</p>
            <p class="text-xs text-slate-500 mt-1">catatan absensi</p>
        </div>

        <div class="stat-card" style="--stat-accent: linear-gradient(90deg, #10b981, #14b8a6)">
            <div class="flex items-center justify-between mb-3">
                <div class="h-10 w-10 rounded-xl bg-emerald-950/70 flex items-center justify-center text-emerald-400">
                    <i class="fas fa-file-medical"></i>
                </div>
                <span class="text-xs text-slate-500">Izin Disetujui</span>
            </div>
            <p class="text-3xl font-bold text-emerald-300">{{ number_format($totalIzin) }}</p>
            <p class="text-xs text-slate-500 mt-1">izin sakit & keperluan</p>
        </div>
    </div>

    {{-- ─── Daily Attendance Chart ─── --}}
    <div class="rounded-3xl bg-slate-900/80 border border-slate-800 p-6 backdrop-blur-sm shadow-2xl">
        <h3 class="text-base font-bold text-slate-100 mb-6">Tren Kehadiran Harian</h3>

        @if($dailyTrend->count() > 0)
        <div class="flex items-end gap-1 h-40 overflow-x-auto pb-2" id="chart-container">
            @foreach($dailyTrend as $day)
            @php
                $maxVal = $dailyTrend->max('total');
                $barHeight = $maxVal > 0 ? round($day->total / $maxVal * 100) : 0;
                $lateRate = $day->total > 0 ? round($day->terlambat / $day->total * 100) : 0;
            @endphp
            <div class="group flex flex-col items-center gap-1 min-w-10 cursor-pointer"
                 title="{{ $day->day }}: {{ $day->total }} hadir, {{ $day->terlambat }} terlambat">
                <div class="relative w-8">
                    <div class="chart-bar w-full rounded-t-lg bg-indigo-500/20 hover:bg-indigo-500/40 transition"
                         style="height: {{ max($barHeight, 4) }}px">
                        @if($day->terlambat > 0)
                        <div class="absolute bottom-0 w-full rounded-t-sm bg-amber-400/60"
                             style="height: {{ max(round($lateRate / 100 * max($barHeight, 4)), 2) }}px"></div>
                        @endif
                    </div>
                </div>
                <span class="text-[9px] text-slate-600 rotate-45 origin-left whitespace-nowrap">
                    {{ \Carbon\Carbon::parse($day->day)->format('d') }}
                </span>
            </div>
            @endforeach
        </div>
        <div class="flex items-center gap-4 mt-2 text-xs text-slate-500">
            <div class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-indigo-500/40"></span> Hadir</div>
            <div class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-amber-400/60"></span> Terlambat</div>
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-12 text-slate-600">
            <i class="fas fa-chart-bar text-4xl mb-3"></i>
            <p class="text-sm">Tidak ada data kehadiran untuk bulan ini</p>
        </div>
        @endif
    </div>

    {{-- ─── Top 10 Least Attended ─── --}}
    @if($topAbsen->count() > 0)
    <div class="rounded-3xl bg-slate-900/80 border border-slate-800 overflow-hidden backdrop-blur-sm shadow-2xl">
        <div class="px-6 py-5 border-b border-slate-800">
            <h3 class="text-base font-bold text-slate-100">🔴 10 Siswa Kehadiran Terendah Bulan Ini</h3>
        </div>
        <div class="divide-y divide-slate-800/60">
            @foreach($topAbsen as $idx => $siswa)
            <div class="px-6 py-4 flex items-center gap-4 hover:bg-slate-800/30 transition">
                <div class="h-8 w-8 rounded-xl flex items-center justify-center text-xs font-bold shrink-0
                    {{ $idx < 3 ? 'bg-rose-950 text-rose-400 border border-rose-800' : 'bg-slate-800 text-slate-500' }}">
                    {{ $idx + 1 }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-slate-100 truncate">{{ $siswa->nama }}</p>
                    <p class="text-xs text-slate-500">{{ $siswa->nis }}</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold {{ $siswa->hadir_count < 10 ? 'text-rose-400' : 'text-slate-300' }}">
                        {{ $siswa->hadir_count }}
                    </p>
                    <p class="text-xs text-slate-500">hari hadir</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
