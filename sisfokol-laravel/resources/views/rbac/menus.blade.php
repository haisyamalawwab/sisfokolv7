@extends('layouts.app')

@section('title', 'RBAC — Menus')
@section('page-title', 'RBAC Builder')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Breadcrumbs / Navigation Tabs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-200 to-purple-200 bg-clip-text text-transparent">Menu Visibility Overrides</h1>
            <p class="text-sm text-slate-400 mt-1">Konfigurasi pengecualian visibilitas menu sidebar per role.</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-800">
        <nav class="flex space-x-8" aria-label="Tabs">
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('rbac.index') }}" class="border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-700 border-b-2 py-4 px-1 text-sm font-medium">Roles & Permissions</a>
            @endif
            <a href="{{ route('rbac.menus') }}" class="border-indigo-500 text-indigo-400 border-b-2 py-4 px-1 text-sm font-medium">Menu Visibility</a>
            <a href="{{ route('rbac.fields') }}" class="border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-700 border-b-2 py-4 px-1 text-sm font-medium">Field Visibility</a>
            <a href="{{ route('rbac.users') }}" class="border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-700 border-b-2 py-4 px-1 text-sm font-medium">User Roles</a>
        </nav>
    </div>

    <!-- Config Form Card -->
    <div class="bg-slate-900 border border-slate-800/60 rounded-2xl p-6 shadow-xl space-y-4">
        <h3 class="text-base font-semibold text-slate-200">Tambah / Perbarui Override Visibilitas Menu</h3>
        <form method="POST" action="{{ route('rbac.menus.update') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            @csrf
            <div>
                <label for="menu_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Pilih Menu</label>
                <select name="menu_id" id="menu_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500" required>
                    @foreach($menus as $m)
                        <option value="{{ $m->id }}">{{ $m->label }} ({{ $m->kode }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="role_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Pilih Role</label>
                <select name="role_id" id="role_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500" required>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}">{{ ucfirst(str_replace('_', ' ', $r->name)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="visible" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Status Visibilitas</label>
                <select name="visible" id="visible" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="show">Show (Tampilkan)</option>
                    <option value="hide">Hide (Sembunyikan)</option>
                    <option value="readonly">Readonly</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm rounded-xl px-4 py-2.5 shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Simpan Override
                </button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-slate-900 border border-slate-800/60 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-800/60 flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-200">Daftar Overrides Aktif</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-950/50 border-b border-slate-800/60">
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Menu</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Role</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Visibilitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($overrides as $o)
                        @php
                            $m = $menus->firstWhere('id', $o->menu_id);
                            $r = $roles->firstWhere('id', $o->role_id);
                        @endphp
                        <tr class="hover:bg-slate-800/20 transition">
                            <td class="p-4">
                                <span class="text-sm font-semibold text-slate-200">{{ $m?->label }}</span>
                                <code class="text-[10px] text-indigo-400 ml-2 bg-indigo-950/40 px-1.5 py-0.5 rounded border border-indigo-900/30">{{ $m?->kode }}</code>
                            </td>
                            <td class="p-4">
                                <span class="text-sm text-slate-300 font-medium">{{ ucfirst(str_replace('_', ' ', $r?->name)) }}</span>
                            </td>
                            <td class="p-4">
                                @if($o->visible === 'show')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Show
                                    </span>
                                @elseif($o->visible === 'hide')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> Hide
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Readonly
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-8 text-center text-sm text-slate-500">
                                <i class="fas fa-folder-open text-2xl mb-2 block text-slate-600"></i>
                                Belum ada override visibilitas menu yang diatur.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
