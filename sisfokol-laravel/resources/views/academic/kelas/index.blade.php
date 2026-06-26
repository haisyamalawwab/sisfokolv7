@extends('layouts.app')

@section('title', 'Akademik — Kelas')
@section('page-title', 'Manajemen Kelas')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-5 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-200 to-purple-200 bg-clip-text text-transparent">Daftar Kelas</h1>
            <p class="text-sm text-slate-400 mt-1">Kelola data rombongan belajar (kelas) per tingkat.</p>
        </div>
        @can('kelas.create')
            <div>
                <a href="{{ route('academic.kelas.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-md shadow-indigo-600/20 transition">
                    <i class="fas fa-plus"></i> Tambah Kelas
                </a>
            </div>
        @endcan
    </div>

    {{-- Search --}}
    <div class="bg-slate-900/40 backdrop-blur-md border border-slate-800/60 rounded-2xl p-5 shadow-lg">
        <form method="GET" action="{{ route('academic.kelas.index') }}" class="flex flex-col sm:flex-row gap-4">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama kelas..." class="w-full pl-10 pr-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-sm font-medium transition flex items-center gap-2 border border-slate-700">
                    Filter
                </button>
                @if($search)
                    <a href="{{ route('academic.kelas.index') }}" class="px-5 py-2.5 bg-slate-950/20 hover:bg-slate-950/40 text-slate-400 hover:text-slate-300 rounded-xl text-sm font-medium transition flex items-center gap-2 border border-slate-800/80">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-5 py-3.5 rounded-xl bg-emerald-950/40 border border-emerald-900/50 text-emerald-300 text-sm">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-slate-900 border border-slate-800/60 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-950/50 border-b border-slate-800/60">
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Kelas</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Tingkat</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Wali Kelas</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Kapasitas</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Cabang</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($kelas as $k)
                        <tr class="hover:bg-slate-800/20 transition">
                            <td class="p-4 text-sm font-semibold text-slate-200">{{ $k->nama }}</td>
                            <td class="p-4 text-sm text-slate-300">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-indigo-950/40 text-indigo-300 border border-indigo-900/50">
                                    Tingkat {{ $k->tingkat }}
                                </span>
                            </td>
                            <td class="p-4 text-sm text-slate-300">{{ $k->waliKelas?->nama ?? '-' }}</td>
                            <td class="p-4 text-sm text-slate-300">{{ $k->kapasitas ?? '-' }}</td>
                            <td class="p-4 text-sm text-slate-300">{{ $k->branch?->nama ?? '-' }}</td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('kelas.update')
                                        <a href="{{ route('academic.kelas.edit', $k) }}" class="p-2 bg-indigo-950/40 hover:bg-indigo-900/40 text-indigo-400 rounded-lg border border-indigo-900/50 transition" title="Edit">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                    @endcan
                                    @can('kelas.delete')
                                        <form action="{{ route('academic.kelas.destroy', $k) }}" method="POST" onsubmit="return confirm('Hapus kelas ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 bg-rose-950/40 hover:bg-rose-900/40 text-rose-400 rounded-lg border border-rose-900/50 transition" title="Hapus">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-sm text-slate-500">
                                Belum ada data kelas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kelas->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/20">
                {{ $kelas->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
