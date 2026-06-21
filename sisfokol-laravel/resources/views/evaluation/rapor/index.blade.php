@extends('layouts.app')

@section('title', 'Cetak Rapor Siswa — SISFOKOL')
@section('page-title', '📈 Rapor Hasil Belajar')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-100">Rapor Siswa</h1>
            <p class="text-sm text-slate-500 mt-0.5">Daftar siswa dan pengunduhan dokumen rapor hasil belajar resmi.</p>
        </div>
        
        {{-- Admin Classroom Filter --}}
        @if($classrooms->count() > 0)
        <form method="GET" action="{{ route('evaluation.rapor.index') }}" class="flex items-center gap-3">
            <select name="classroom_id" onchange="this.form.submit()"
                class="px-4 py-2.5 rounded-2xl bg-slate-800 border border-slate-700 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                @foreach($classrooms as $c)
                    <option value="{{ $c->id }}" {{ $classroom && $classroom->id == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </form>
        @endif
    </div>

    {{-- Student Grid --}}
    @if($classroom)
        <div class="rounded-3xl bg-slate-900/80 border border-slate-800 overflow-hidden backdrop-blur-sm shadow-2xl">
            <div class="px-6 py-5 border-b border-slate-800 flex justify-between items-center">
                <h3 class="text-base font-bold text-slate-100">Daftar Siswa Kelas {{ $classroom->name }}</h3>
                <span class="px-3 py-1 rounded-full bg-indigo-950 text-indigo-400 text-xs font-semibold">
                    {{ $students->count() }} Siswa
                </span>
            </div>
            
            @if($students->count() > 0)
                <div class="divide-y divide-slate-800/60">
                    @foreach($students as $idx => $student)
                        <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 hover:bg-slate-800/20 transition">
                            <div class="flex items-center gap-4">
                                <div class="h-9 w-9 rounded-xl bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-500 border border-slate-700">
                                    {{ $idx + 1 }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-200">{{ $student->name }}</p>
                                    <p class="text-xs text-slate-500">NIS: {{ $student->nis }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <a href="{{ route('evaluation.rapor.show', $student->id) }}" 
                                   class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition flex items-center gap-1.5 border border-slate-700">
                                    <i class="fas fa-eye text-slate-400"></i> Preview Rapor
                                </a>
                                <a href="{{ route('evaluation.rapor.pdf', $student->id) }}" 
                                   target="_blank"
                                   class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-md shadow-indigo-600/10">
                                    <i class="fas fa-file-pdf"></i> Cetak PDF
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-16 text-slate-600 space-y-3">
                    <i class="fas fa-users-slash text-4xl"></i>
                    <p class="text-sm">Tidak ada siswa terdaftar di kelas ini</p>
                </div>
            @endif
        </div>
    @else
        <div class="rounded-3xl bg-slate-900/80 border border-slate-800 p-12 text-center text-slate-500 space-y-4 shadow-2xl">
            <div class="h-14 w-14 rounded-2xl bg-slate-850/60 flex items-center justify-center mx-auto text-indigo-400 text-xl border border-slate-850">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="max-w-md mx-auto">
                <h3 class="font-bold text-slate-300 text-base">Wali Kelas Tidak Terdeteksi</h3>
                <p class="text-sm text-slate-500 mt-1">Akun Anda belum dikonfigurasikan sebagai Wali Kelas pada semester ini, silakan hubungi Administrator sekolah.</p>
            </div>
        </div>
    @endif

</div>
@endsection
