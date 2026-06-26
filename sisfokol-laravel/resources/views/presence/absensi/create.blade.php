@extends('layouts.app')

@section('title', 'Pencatatan Absensi — SISFOKOL')
@section('page-title', '📋 Pencatatan Absensi per Rombongan Belajar')

@section('content')
<div class="max-w-5xl mx-auto space-y-5">

    {{-- ─── Header ─── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-100">Absensi Harian</h1>
            <p class="text-sm text-slate-500 mt-0.5">Pilih kelas dan tandai status kehadiran siswa</p>
        </div>
        <a href="{{ route('presence.absensi.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold transition">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- ─── Errors ─── --}}
    @if ($errors->any())
    <div class="rounded-2xl border border-rose-800/60 bg-rose-950/40 px-5 py-4 text-rose-300">
        <div class="flex items-center gap-2 font-semibold mb-2"><i class="fas fa-exclamation-triangle"></i> Ada kesalahan:</div>
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    @if($kelasList->isEmpty())
    <div class="rounded-2xl border border-slate-700 bg-slate-900/60 px-6 py-12 text-center">
        <i class="fas fa-school text-4xl text-slate-600 mb-3 block"></i>
        <p class="text-slate-500 text-sm">Belum ada data kelas. Silakan tambah kelas terlebih dahulu.</p>
    </div>
    @else

    {{-- ─── FORM WRAPPER ─── --}}
    <form action="{{ route('presence.absensi.store') }}" method="POST" id="form-absensi">
        @csrf

        {{-- ─── Bar atas: Tanggal + Pilih Kelas ─── --}}
        <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-4 backdrop-blur-sm mb-4">
            <div class="flex flex-wrap gap-4 items-end">

                {{-- Tanggal --}}
                <div class="min-w-44">
                    <label for="date" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                        <i class="fas fa-calendar-day mr-1 text-indigo-400"></i> Tanggal
                    </label>
                    <input type="date" id="date" name="date"
                        value="{{ old('date', today()->toDateString()) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-100 text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition">
                </div>

                {{-- Pilih Kelas (tab-style select) --}}
                <div class="flex-1 min-w-60">
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                        <i class="fas fa-door-open mr-1 text-indigo-400"></i> Kelas / Rombongan Belajar
                    </label>
                    <div class="flex flex-wrap gap-2" id="kelas-tabs">
                        @foreach($kelasList as $kelas)
                        @php $siswaCount = $kelas->kelasSiswa->count(); @endphp
                        <button type="button"
                            data-kelas-id="{{ $kelas->id }}"
                            onclick="switchKelas({{ $kelas->id }})"
                            class="kelas-tab px-3.5 py-2 rounded-xl text-xs font-semibold border transition-all duration-150
                                {{ $selectedKelasId == $kelas->id
                                    ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-500/20'
                                    : 'bg-slate-800 border-slate-700 text-slate-400 hover:border-slate-600 hover:text-slate-200' }}">
                            {{ $kelas->nama }}
                            <span class="ml-1 opacity-60 text-[10px]">({{ $siswaCount }})</span>
                        </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="kelas_id" id="kelas-id-input" value="{{ old('kelas_id', $selectedKelasId) }}">
                </div>

                {{-- Shortcut: Semua Hadir --}}
                <div class="flex gap-2">
                    <button type="button" onclick="setAll('hadir')"
                        class="px-3.5 py-2.5 rounded-xl bg-emerald-900/50 hover:bg-emerald-900 border border-emerald-800/60 text-emerald-400 text-xs font-semibold transition whitespace-nowrap">
                        <i class="fas fa-check-double mr-1"></i> Semua Hadir
                    </button>
                </div>
            </div>

            {{-- Legend --}}
            <div class="flex flex-wrap gap-4 mt-3 pt-3 border-t border-slate-800">
                @foreach([['hadir','emerald','Hadir'],['alpha','rose','Alpha'],['ijin','blue','Ijin'],['sakit','amber','Sakit']] as [$val,$color,$label])
                <div class="flex items-center gap-1.5 text-xs text-slate-400">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-{{ $color }}-500"></span>
                    {{ $label }}
                </div>
                @endforeach
                <div class="ml-auto flex gap-4 text-xs text-slate-400 font-mono" id="summary">
                    <span><span class="text-emerald-400 font-bold" id="cnt-hadir">0</span> H</span>
                    <span><span class="text-rose-400 font-bold" id="cnt-alpha">0</span> A</span>
                    <span><span class="text-blue-400 font-bold" id="cnt-ijin">0</span> I</span>
                    <span><span class="text-amber-400 font-bold" id="cnt-sakit">0</span> S</span>
                </div>
            </div>
        </div>

        {{-- ─── Panel per Kelas ─── --}}
        @foreach($kelasList as $kelas)
        @php
            $siswaDiKelas = $kelas->kelasSiswa->sortBy('siswa.nama');
        @endphp
        <div id="panel-{{ $kelas->id }}"
            class="kelas-panel {{ $selectedKelasId == $kelas->id ? '' : 'hidden' }}">

            @if($siswaDiKelas->isEmpty())
            <div class="rounded-2xl border border-slate-700 bg-slate-900/60 px-6 py-10 text-center">
                <i class="fas fa-users text-3xl text-slate-600 mb-2 block"></i>
                <p class="text-slate-500 text-sm">Belum ada siswa di kelas <strong class="text-slate-400">{{ $kelas->nama }}</strong>.</p>
            </div>
            @else
            <div class="rounded-3xl bg-slate-900/80 border border-slate-800 overflow-hidden backdrop-blur-sm shadow-2xl">
                {{-- Kelas header --}}
                <div class="px-5 py-3.5 border-b border-slate-800 flex items-center justify-between bg-slate-800/40">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-700 flex items-center justify-center text-white font-bold text-sm">
                            {{ $kelas->tingkat }}
                        </div>
                        <div>
                            <p class="font-semibold text-slate-100 text-sm">{{ $kelas->nama }}</p>
                            <p class="text-xs text-slate-500">{{ $siswaDiKelas->count() }} siswa</p>
                        </div>
                    </div>
                    @if($kelas->waliKelas)
                    <p class="text-xs text-slate-500">
                        <i class="fas fa-chalkboard-teacher mr-1"></i>
                        {{ $kelas->waliKelas->nama }}
                    </p>
                    @endif
                </div>

                {{-- Tabel siswa --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="border-b border-slate-800 bg-slate-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase w-8">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nama Siswa</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase w-20 text-emerald-400">Hadir</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase w-20 text-rose-400">Alpha</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase w-20 text-blue-400">Ijin</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase w-20 text-amber-400">Sakit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @foreach($siswaDiKelas as $idx => $ks)
                            @php
                                $siswa = $ks->siswa;
                                if (!$siswa) continue;
                                $fieldName = "status[{$siswa->id}]";
                                $oldVal = old($fieldName, 'hadir');
                            @endphp
                            <tr class="hover:bg-slate-800/20 transition group absensi-row"
                                id="row-{{ $siswa->id }}"
                                data-siswa-id="{{ $siswa->id }}">
                                <td class="px-4 py-3 text-slate-600 text-xs font-mono">{{ $ks->no_urut ?? $idx + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="row-avatar h-8 w-8 rounded-xl flex items-center justify-center text-white font-bold text-xs shrink-0 transition-all duration-200"
                                            style="background: linear-gradient(135deg, #6366f1, #8b5cf6)">
                                            {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-100 text-sm leading-tight">{{ $siswa->nama }}</p>
                                            <p class="text-xs text-slate-500">{{ $siswa->nis }}</p>
                                        </div>
                                    </div>
                                </td>

                                @foreach([
                                    ['hadir','emerald','check'],
                                    ['alpha','rose','times'],
                                    ['ijin','blue','sign-out-alt'],
                                    ['sakit','amber','thermometer-half'],
                                ] as [$val, $color, $icon])
                                <td class="px-4 py-3 text-center">
                                    <label class="cursor-pointer inline-flex items-center justify-center">
                                        <input type="radio"
                                            name="{{ $fieldName }}"
                                            value="{{ $val }}"
                                            {{ $oldVal === $val ? 'checked' : '' }}
                                            class="sr-only peer"
                                            onchange="updateRow({{ $siswa->id }}, '{{ $val }}')">
                                        <span class="w-7 h-7 rounded-full border-2 border-slate-700 flex items-center justify-center transition-all duration-150
                                            peer-checked:border-{{ $color }}-500 peer-checked:bg-{{ $color }}-500
                                            peer-checked:shadow-md peer-checked:shadow-{{ $color }}-500/30
                                            hover:border-{{ $color }}-600/50">
                                            <i class="fas fa-{{ $icon }} text-white text-[10px] opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                                        </span>
                                    </label>
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Footer: Submit --}}
                <div class="px-5 py-4 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-900/40">
                    <p class="text-xs text-slate-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Siswa yang tidak dipilih otomatis dianggap <span class="text-emerald-400 font-semibold">Hadir</span>
                    </p>
                    <div class="flex gap-3">
                        <a href="{{ route('presence.absensi.index') }}"
                            class="px-5 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold transition">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-6 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-sm font-semibold shadow-lg shadow-indigo-500/20 transition flex items-center gap-2">
                            <i class="fas fa-save"></i> Simpan Absensi
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endforeach

    </form>
    @endif
</div>

@push('scripts')
<script>
const STATUS_STYLE = {
    hadir: { bg: 'linear-gradient(135deg, #10b981, #059669)', rowCls: '' },
    alpha: { bg: 'linear-gradient(135deg, #f43f5e, #e11d48)', rowCls: 'bg-rose-950/30' },
    ijin:  { bg: 'linear-gradient(135deg, #3b82f6, #2563eb)', rowCls: 'bg-blue-950/30' },
    sakit: { bg: 'linear-gradient(135deg, #f59e0b, #d97706)', rowCls: 'bg-amber-950/30' },
};

function updateRow(siswaId, status) {
    const row    = document.getElementById('row-' + siswaId);
    const avatar = row.querySelector('.row-avatar');
    row.classList.remove('bg-rose-950/30', 'bg-blue-950/30', 'bg-amber-950/30');
    avatar.style.background = STATUS_STYLE[status].bg;
    if (STATUS_STYLE[status].rowCls) row.classList.add(STATUS_STYLE[status].rowCls);
    updateSummary();
}

function setAll(status) {
    // Hanya terapkan pada panel yang aktif (visible)
    const activePanel = document.querySelector('.kelas-panel:not(.hidden)');
    if (!activePanel) return;
    activePanel.querySelectorAll(`input[type="radio"][value="${status}"]`).forEach(radio => {
        radio.checked = true;
        const match = radio.name.match(/\[(\d+)\]/);
        if (match) updateRow(parseInt(match[1]), status);
    });
}

function updateSummary() {
    const counts = { hadir: 0, alpha: 0, ijin: 0, sakit: 0 };
    const activePanel = document.querySelector('.kelas-panel:not(.hidden)');
    if (!activePanel) return;
    activePanel.querySelectorAll('input[type="radio"]:checked').forEach(r => {
        counts[r.value] = (counts[r.value] || 0) + 1;
    });
    document.getElementById('cnt-hadir').textContent = counts.hadir;
    document.getElementById('cnt-alpha').textContent = counts.alpha;
    document.getElementById('cnt-ijin').textContent  = counts.ijin;
    document.getElementById('cnt-sakit').textContent = counts.sakit;
}

function switchKelas(kelasId) {
    // Update hidden input
    document.getElementById('kelas-id-input').value = kelasId;

    // Show/hide panels
    document.querySelectorAll('.kelas-panel').forEach(p => p.classList.add('hidden'));
    const panel = document.getElementById('panel-' + kelasId);
    if (panel) panel.classList.remove('hidden');

    // Update tab styles
    document.querySelectorAll('.kelas-tab').forEach(btn => {
        const active = parseInt(btn.dataset.kelasId) === kelasId;
        btn.className = btn.className
            .replace('bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-500/20', '')
            .replace('bg-slate-800 border-slate-700 text-slate-400 hover:border-slate-600 hover:text-slate-200', '');
        if (active) {
            btn.classList.add('bg-indigo-600', 'border-indigo-500', 'text-white', 'shadow-lg', 'shadow-indigo-500/20');
            btn.classList.remove('bg-slate-800', 'border-slate-700', 'text-slate-400');
        } else {
            btn.classList.add('bg-slate-800', 'border-slate-700', 'text-slate-400');
            btn.classList.remove('bg-indigo-600', 'border-indigo-500', 'text-white', 'shadow-lg', 'shadow-indigo-500/20');
        }
    });

    updateSummary();
}

// Init on load
document.addEventListener('DOMContentLoaded', () => {
    // Set visual state for all checked radios
    document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
        const match = radio.name.match(/\[(\d+)\]/);
        if (match) updateRow(parseInt(match[1]), radio.value);
    });
    updateSummary();
});
</script>
@endpush
@endsection
