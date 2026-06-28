@extends('layouts.app')

@section('title', 'RBAC — Fields')
@section('page-title', 'RBAC Builder')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Breadcrumbs / Navigation Tabs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-200 to-purple-200 bg-clip-text text-transparent">Field Visibility Overrides</h1>
            <p class="text-sm text-slate-400 mt-1">Konfigurasi visibilitas dan proteksi field database sensitif per role.</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-800">
        <nav class="flex space-x-8" aria-label="Tabs">
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('rbac.index') }}" class="border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-700 border-b-2 py-4 px-1 text-sm font-medium">Roles & Permissions</a>
            @endif
            <a href="{{ route('rbac.menus') }}" class="border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-700 border-b-2 py-4 px-1 text-sm font-medium">Menu Visibility</a>
            <a href="{{ route('rbac.fields') }}" class="border-indigo-500 text-indigo-400 border-b-2 py-4 px-1 text-sm font-medium">Field Visibility</a>
            <a href="{{ route('rbac.users') }}" class="border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-700 border-b-2 py-4 px-1 text-sm font-medium">User Roles</a>
        </nav>
    </div>

    <!-- Info Box -->
    <div class="p-4 rounded-2xl bg-indigo-950/20 border border-indigo-800/40 text-indigo-300 text-sm flex items-start gap-3">
        <i class="fas fa-info-circle text-lg mt-0.5"></i>
        <div>
            <span class="font-semibold">Default Security Policy:</span>
            <p class="mt-0.5 text-indigo-400/90">Secara default, field dengan kategori <code>sensitif</code> dan <code>sangat_sensitif</code> disembunyikan (hidden) untuk seluruh role non-admin. Tambahkan override spesifik di bawah ini jika ingin membuka akses baca (visible) atau baca saja (readonly).</p>
        </div>
    </div>

    <!-- Config Form Card -->
    <div class="bg-slate-900 border border-slate-800/60 rounded-2xl p-6 shadow-xl space-y-4">
        <h3 class="text-base font-semibold text-slate-200">Tambah / Perbarui Override Visibilitas Field</h3>
        <form method="POST" action="{{ route('rbac.fields.update') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            @csrf
            <div>
                <label for="field_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Pilih Field</label>
                <select name="field_id" id="field_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500" required>
                    @foreach($fields as $f)
                        <option value="{{ $f->id }}">{{ $f->label }} ({{ $f->kode }}) — Default: {{ $f->default_visibility }}</option>
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
                <label for="visibility" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Akses Visibilitas</label>
                <select name="visibility" id="visibility" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="visible">Visible (Dapat Dilihat)</option>
                    <option value="hidden">Hidden (Disembunyikan)</option>
                    <option value="readonly">Readonly (Hanya Baca)</option>
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
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Field</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Role</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Visibilitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($overrides as $o)
                        @php
                            $f = $fields->firstWhere('id', $o->field_id);
                            $r = $roles->firstWhere('id', $o->role_id);
                        @endphp
                        <tr class="hover:bg-slate-800/20 transition">
                            <td class="p-4">
                                <span class="text-sm font-semibold text-slate-200">{{ $f?->label }}</span>
                                <code class="text-[10px] text-indigo-400 ml-2 bg-indigo-950/40 px-1.5 py-0.5 rounded border border-indigo-900/30">{{ $f?->kode }}</code>
                            </td>
                            <td class="p-4">
                                <span class="text-sm text-slate-300 font-medium">{{ ucfirst(str_replace('_', ' ', $r?->name)) }}</span>
                            </td>
                            <td class="p-4">
                                @if($o->visibility === 'visible')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Visible
                                    </span>
                                @elseif($o->visibility === 'hidden')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> Hidden
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
                                <i class="fas fa-eye-slash text-2xl mb-2 block text-slate-600"></i>
                                Belum ada override visibilitas field yang diatur.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
