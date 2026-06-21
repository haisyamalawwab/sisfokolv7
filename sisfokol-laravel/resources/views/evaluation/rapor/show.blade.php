@extends('layouts.app')

@section('title', 'Preview Rapor - ' . $student->name . ' — SISFOKOL')
@section('page-title', '📈 Preview Rapor Siswa')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Control bar --}}
    <div class="flex items-center justify-between gap-4">
        <a href="{{ route('evaluation.rapor.index') }}" 
           class="px-4 py-2.5 rounded-2xl bg-slate-800 border border-slate-700 hover:bg-slate-700 text-slate-200 text-sm font-semibold transition">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
        </a>
        <a href="{{ route('evaluation.rapor.pdf', $student->id) }}" 
           target="_blank"
           class="px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition flex items-center gap-2 shadow-lg shadow-indigo-600/20">
            <i class="fas fa-print"></i> Cetak Rapor (PDF)
        </a>
    </div>

    {{-- Report Paper layout --}}
    <div class="rounded-3xl bg-white border border-slate-200 text-slate-900 p-10 shadow-2xl space-y-8">
        
        {{-- Header KOP --}}
        <div class="border-b-[4px] border-double border-slate-900 pb-4 text-center">
            <h2 class="text-xl font-bold uppercase tracking-wider">SMA DEMO SISFOKOL</h2>
            <p class="text-xs text-slate-600 italic mt-0.5">Jl. Pendidikan No. 1, Kota Demo • Telp: 021-1234567 • Email: info@smademo.sch.id</p>
        </div>

        <h3 class="text-center text-lg font-bold uppercase tracking-wide underline">Rapor Hasil Belajar Peserta Didik</h3>

        {{-- Student Profile Table --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-slate-800">
            <div class="space-y-1">
                <div class="flex"><span class="w-32 text-slate-500">Nama Siswa</span><span class="w-4">:</span><span class="font-bold text-slate-950">{{ $student->name }}</span></div>
                <div class="flex"><span class="w-32 text-slate-500">NIS / NISN</span><span class="w-4">:</span><span>{{ $student->nis }} / {{ $student->nisn ?? '-' }}</span></div>
                <div class="flex"><span class="w-32 text-slate-500">Sekolah</span><span class="w-4">:</span><span>SMA Demo Sisfokol</span></div>
            </div>
            <div class="space-y-1">
                <div class="flex"><span class="w-32 text-slate-500">Kelas</span><span class="w-4">:</span><span>{{ $classroom->name }}</span></div>
                <div class="flex"><span class="w-32 text-slate-500">Semester</span><span class="w-4">:</span><span>{{ $semester->nama }} ({{ $semester->nama == 1 ? 'Ganjil' : 'Genap' }})</span></div>
                <div class="flex"><span class="w-32 text-slate-500">Tahun Ajaran</span><span class="w-4">:</span><span>{{ $academicYear->name }}</span></div>
            </div>
        </div>

        {{-- Grades Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border border-slate-900 border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-800 text-xs font-bold uppercase border-b border-slate-900">
                        <th class="p-3 border-r border-slate-900 text-center w-12">No</th>
                        <th class="p-3 border-r border-slate-900 w-1/4">Mata Pelajaran</th>
                        <th class="p-3 border-r border-slate-900 text-center w-24">Nilai Akhir</th>
                        <th class="p-3 border-r border-slate-900 text-center w-20">Predikat</th>
                        <th class="p-3">Capaian Kompetensi / Deskripsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900 text-sm text-slate-900">
                    @forelse($scores as $idx => $score)
                        <tr>
                            <td class="p-3 border-r border-slate-900 text-center">{{ $idx + 1 }}</td>
                            <td class="p-3 border-r border-slate-900 font-medium">{{ $score->subject->name }}</td>
                            <td class="p-3 border-r border-slate-900 text-center font-bold">{{ number_format($score->score, 2) }}</td>
                            <td class="p-3 border-r border-slate-900 text-center font-semibold">{{ $score->predicate }}</td>
                            <td class="p-3 text-slate-700 leading-relaxed">{{ $score->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center italic text-slate-500 bg-slate-50">Belum ada data nilai semester ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Attendance & Notes --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
            
            {{-- Attendance --}}
            <div class="space-y-3">
                <h4 class="font-bold text-slate-800 border-b border-slate-200 pb-1 flex items-center gap-1.5">
                    <i class="fas fa-calendar-check text-slate-600"></i> Ketidakhadiran
                </h4>
                <table class="w-full text-left border border-slate-900 border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100 text-slate-800 font-bold border-b border-slate-900">
                            <th class="p-2 border-r border-slate-900">Keterangan</th>
                            <th class="p-2 text-center w-24">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900 text-slate-700">
                        <tr>
                            <td class="p-2 border-r border-slate-900">Sakit (S)</td>
                            <td class="p-2 text-center font-semibold">{{ $attendance['sick'] }} hari</td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r border-slate-900">Izin (I)</td>
                            <td class="p-2 text-center font-semibold">{{ $attendance['permission'] }} hari</td>
                        </tr>
                        <tr>
                            <td class="p-2 border-r border-slate-900">Tanpa Keterangan (A)</td>
                            <td class="p-2 text-center font-semibold">{{ $attendance['absent'] }} hari</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Notes --}}
            <div class="space-y-3">
                <h4 class="font-bold text-slate-800 border-b border-slate-200 pb-1 flex items-center gap-1.5">
                    <i class="fas fa-comment-dots text-slate-600"></i> Catatan Wali Kelas
                </h4>
                <div class="border border-slate-900 rounded-xl p-4 bg-slate-50 italic text-slate-800 text-sm leading-relaxed">
                    "{{ $note }}"
                </div>
            </div>

        </div>

        {{-- Signature Section --}}
        <div class="pt-8 grid grid-cols-3 gap-4 text-center text-xs text-slate-800">
            <div>
                <p>Mengetahui,</p>
                <p>Orang Tua/Wali Siswa</p>
                <div class="h-16"></div>
                <p class="underline">..........................................</p>
            </div>
            <div></div>
            <div>
                <p>Kota Demo, {{ now()->format('d F Y') }}</p>
                <p>Wali Kelas</p>
                <div class="h-16 flex items-center justify-center font-bold italic text-slate-400">
                    Tanda Tangan
                </div>
                <p class="font-bold underline">{{ $classroom->homeroomTeacher?->name ?? 'Wali Kelas' }}</p>
            </div>
        </div>

    </div>

</div>
@endsection
