@extends('layouts.app')

@section('title', 'Akademik — Tambah Kelas')
@section('page-title', 'Tambah Kelas Baru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-5 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-200 to-purple-200 bg-clip-text text-transparent">Tambah Kelas Baru</h1>
            <p class="text-sm text-slate-400 mt-1">Masukkan informasi kelas secara lengkap.</p>
        </div>
        <a href="{{ route('academic.kelas.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-slate-100 rounded-xl text-sm font-medium transition border border-slate-700">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="bg-slate-900 border border-slate-800/60 rounded-2xl p-6 shadow-xl">
        <form method="POST" action="{{ route('academic.kelas.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Nama Kelas --}}
                <div class="md:col-span-2">
                    <label for="nama" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Nama Kelas <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama') }}" placeholder="Contoh: VII-A, X IPA 1" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('nama') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                    @error('nama')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tingkat --}}
                <div>
                    <label for="tingkat" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Tingkat <span class="text-rose-500">*</span></label>
                    <input type="number" name="tingkat" id="tingkat" value="{{ old('tingkat') }}" min="1" max="12" placeholder="1 - 12" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('tingkat') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                    @error('tingkat')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kapasitas --}}
                <div>
                    <label for="kapasitas" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Kapasitas</label>
                    <input type="number" name="kapasitas" id="kapasitas" value="{{ old('kapasitas') }}" min="1" max="100" placeholder="Maks. siswa" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('kapasitas') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                    @error('kapasitas')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Wali Kelas --}}
                <div>
                    <label for="wali_kelas_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Wali Kelas</label>
                    <select name="wali_kelas_id" id="wali_kelas_id" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('wali_kelas_id') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                        <option value="">-- Pilih Wali Kelas --</option>
                        @foreach($gurus as $guru)
                            <option value="{{ $guru->id }}" {{ old('wali_kelas_id') == $guru->id ? 'selected' : '' }}>{{ $guru->nama }}</option>
                        @endforeach
                    </select>
                    @error('wali_kelas_id')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Cabang --}}
                <div>
                    <label for="branch_id" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Cabang</label>
                    <select name="branch_id" id="branch_id" class="w-full px-4 py-2.5 bg-slate-950/50 border @error('branch_id') border-rose-500 @else border-slate-800 @enderror rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm">
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->nama }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <a href="{{ route('academic.kelas.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-800 text-slate-400 hover:text-slate-200 hover:bg-slate-800/55 transition text-sm font-medium">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
