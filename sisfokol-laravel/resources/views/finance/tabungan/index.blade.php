@extends('layouts.app')

@section('title', 'Keuangan — Tabungan Siswa')
@section('page-title', 'Tabungan Siswa')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between pb-5 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-200 to-purple-200 bg-clip-text text-transparent">Rekening Tabungan Siswa</h1>
            <p class="text-sm text-slate-400 mt-1">Daftar saldo dan nomor rekening tabungan siswa aktif.</p>
        </div>
        @can('create', $config->model)
        <a href="{{ route('finance.tabungan.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-md shadow-indigo-600/20 transition">
            <i class="fas fa-user-plus"></i> Buka Rekening Baru
        </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-950/30 border border-emerald-800/60 rounded-xl text-emerald-400 text-sm flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-950/30 border border-rose-800/60 rounded-xl text-rose-400 text-sm flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- Livewire table (index-only; create uses TabunganMutasiService, show handles setor/tarik) --}}
    @livewire('crudlfix.crudlfix-table', [
        'model' => $config->model,
        'route' => $config->route,
        'search' => $config->search ?? [],
        'with' => $config->with ?? [],
        'columns' => [
            'siswa.nama' => 'Siswa',
            'no_rekening' => 'No. Rekening',
        ],
        'perPage' => $config->perPage ?? 15,
        'authorize' => $config->authorize,
        'authType' => $config->authType,
        'inlineEdit' => false,
        'showEdit' => false,
    ])
</div>
@endsection
