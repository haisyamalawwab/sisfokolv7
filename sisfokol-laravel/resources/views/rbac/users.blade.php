@extends('layouts.app')

@section('title', 'RBAC — Users')
@section('page-title', 'RBAC Builder')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ openModal: null }">
    <!-- Breadcrumbs / Navigation Tabs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-200 to-purple-200 bg-clip-text text-transparent">User Role Assignment</h1>
            <p class="text-sm text-slate-400 mt-1">Kelola pembagian peran/role untuk masing-masing staf dan pengguna.</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-800">
        <nav class="flex space-x-8" aria-label="Tabs">
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('rbac.index') }}" class="border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-700 border-b-2 py-4 px-1 text-sm font-medium">Roles & Permissions</a>
            @endif
            <a href="{{ route('rbac.menus') }}" class="border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-700 border-b-2 py-4 px-1 text-sm font-medium">Menu Visibility</a>
            <a href="{{ route('rbac.fields') }}" class="border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-700 border-b-2 py-4 px-1 text-sm font-medium">Field Visibility</a>
            <a href="{{ route('rbac.users') }}" class="border-indigo-500 text-indigo-400 border-b-2 py-4 px-1 text-sm font-medium">User Roles</a>
        </nav>
    </div>

    <!-- Users Table Card -->
    <div class="bg-slate-900 border border-slate-800/60 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-950/50 border-b border-slate-800/60">
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Username</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Roles</th>
                        <th class="p-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/40">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-800/20 transition">
                            <td class="p-4">
                                <span class="text-sm font-semibold text-slate-200">{{ $u->username }}</span>
                            </td>
                            <td class="p-4">
                                <span class="text-sm text-slate-300">{{ $u->nama }}</span>
                            </td>
                            <td class="p-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($u->roles as $r)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-800 text-indigo-400 border border-indigo-900/50">
                                            {{ ucfirst(str_replace('_', ' ', $r->name)) }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-500 italic">No roles</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="p-4 text-right">
                                <button @click="openModal = {{ $u->id }}" class="bg-indigo-950 text-indigo-400 border border-indigo-900/50 hover:bg-indigo-900 hover:text-indigo-200 transition px-3.5 py-1.5 rounded-xl text-xs font-medium">
                                    Set Roles
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-sm text-slate-500">
                                Tidak ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($users->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/20">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Modals for User Role assignment (Alpine.js integrated) -->
    @foreach($users as $u)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-show="openModal === {{ $u->id }}" style="display: none;" x-cloak>
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="openModal = null"></div>

            <!-- Modal Wrapper -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl transition-all" @click.away="openModal = null">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                        <h3 class="text-lg font-bold text-slate-200">Assign Roles: {{ $u->nama }}</h3>
                        <button class="text-slate-500 hover:text-slate-300 focus:outline-none" @click="openModal = null">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <form method="POST" action="{{ route('rbac.users.roles', $u) }}" class="mt-6 space-y-6">
                        @csrf
                        <div class="space-y-3">
                            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Pilih Role Pengguna</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($roles as $r)
                                    @php
                                        // Spatie roles relation scoped properly
                                        $has = $u->roles->contains($r->id);
                                    @endphp
                                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-800 hover:border-slate-700 bg-slate-950/30 hover:bg-slate-950/60 cursor-pointer transition select-none">
                                        <input type="checkbox" name="roles[]" value="{{ $r->id }}" class="h-4 w-4 rounded border-slate-700 bg-slate-800 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900 transition" @if($has) checked @endif>
                                        <div>
                                            <span class="text-sm font-semibold text-slate-200 block">{{ ucfirst(str_replace('_', ' ', $r->name)) }}</span>
                                            <span class="text-[9px] text-slate-500 uppercase tracking-wider">{{ $r->guard_name }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                            <button type="button" @click="openModal = null" class="px-4 py-2.5 rounded-xl border border-slate-800 text-slate-400 hover:text-slate-200 hover:bg-slate-800 transition text-sm font-medium">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
