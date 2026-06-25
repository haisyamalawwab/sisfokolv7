<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor Hasil Belajar — {{ $student->name }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif, sans-serif;
            font-size: 12pt;
            color: #111;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        .school-name {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        .school-address {
            font-size: 9pt;
            text-align: center;
            font-style: italic;
        }
        .title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
            text-decoration: underline;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            font-size: 11pt;
        }
        .info-table td {
            padding: 2px 4px;
        }
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 11pt;
        }
        .grades-table th, .grades-table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }
        .grades-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .attendance-table {
            width: 50%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 11pt;
        }
        .attendance-table th, .attendance-table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        .attendance-table th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .note-container {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 35px;
            font-size: 11pt;
            background-color: #fafafa;
        }
        .note-title {
            font-weight: bold;
            margin-bottom: 5px;
            text-decoration: underline;
        }
        .signatures-table {
            width: 100%;
            margin-top: 30px;
            font-size: 11pt;
        }
        .signatures-table td {
            text-align: center;
            width: 33%;
            padding-bottom: 60px;
        }
    </style>
</head>
<body>

    {{-- School Kop --}}
    <table class="header-table">
        <tr>
            <td style="text-align: center;">
                <div class="school-name">{{ strtoupper($schoolProfile?->name ?? 'NAMA SEKOLAH') }}</div>
                <div class="school-address">
                    {{ $schoolProfile?->address ?? 'Alamat Sekolah' }},
                    {{ $schoolProfile?->city ?? 'Kota' }} &bull;
                    Telp: {{ $schoolProfile?->phone ?? '-' }} &bull;
                    Email: {{ $schoolProfile?->email ?? '-' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="title">Rapor Hasil Belajar Peserta Didik</div>

    {{-- Student Info --}}
    <table class="info-table">
        <tr>
            <td style="width: 15%;">Nama Siswa</td>
            <td style="width: 2%;">:</td>
            <td style="width: 43%; font-weight: bold;">{{ $student->name }}</td>
            <td style="width: 15%;">Kelas</td>
            <td style="width: 2%;">:</td>
            <td style="width: 23%;">{{ $classroom->name }}</td>
        </tr>
        <tr>
            <td>NIS / NISN</td>
            <td>:</td>
            <td>{{ $student->nis }} / {{ $student->nisn ?? '-' }}</td>
            <td>Semester</td>
            <td>:</td>
            <td>{{ $semester->nama }} ({{ $semester->nama == 1 ? 'Ganjil' : 'Genap' }})</td>
        </tr>
        <tr>
            <td>Sekolah</td>
            <td>:</td>
            <td>{{ $schoolProfile?->name ?? 'Nama Sekolah' }}</td>
            <td>Tahun Ajaran</td>
            <td>:</td>
            <td>{{ $academicYear->name }}</td>
        </tr>
    </table>

    {{-- Grades --}}
    <table class="grades-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 30%;">Mata Pelajaran</th>
                <th style="width: 12%;">Nilai Akhir</th>
                <th style="width: 10%;">Predikat</th>
                <th style="width: 43%;">Capaian Kompetensi / Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($scores as $idx => $score)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $score->subject->name }}</td>
                    <td class="text-center font-bold">{{ number_format($score->score, 2) }}</td>
                    <td class="text-center">{{ $score->predicate }}</td>
                    <td>{{ $score->description }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="font-style: italic; color: #666; padding: 15px;">
                        Belum ada data nilai semester ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Attendance & Notes --}}
    <table style="width: 100%; border: none;">
        <tr>
            <td style="width: 48%; vertical-align: top; border: none;">
                <div style="font-weight: bold; margin-bottom: 5px;">Ketidakhadiran :</div>
                <table class="attendance-table" style="width: 100%;">
                    <tr>
                        <th style="width: 60%;">Keterangan</th>
                        <th style="width: 40%; text-align: center;">Jumlah Hari</th>
                    </tr>
                    <tr>
                        <td>Sakit (S)</td>
                        <td class="text-center">{{ $attendance['sick'] }} hari</td>
                    </tr>
                    <tr>
                        <td>Izin (I)</td>
                        <td class="text-center">{{ $attendance['permission'] }} hari</td>
                    </tr>
                    <tr>
                        <td>Tanpa Keterangan (A)</td>
                        <td class="text-center">{{ $attendance['absent'] }} hari</td>
                    </tr>
                </table>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; vertical-align: top; border: none;">
                <div class="note-container">
                    <div class="note-title">Catatan Wali Kelas</div>
                    <p style="margin: 0; font-style: italic;">"{{ $note }}"</p>
                </div>
            </td>
        </tr>
    </table>

    {{-- Signatures --}}
    <table class="signatures-table">
        <tr>
            <td>
                Mengetahui,<br>
                Orang Tua/Wali Siswa
                <br><br><br><br>
                ..........................................
            </td>
            <td></td>
            <td>
                {{ $schoolProfile?->city ?? 'Kota' }}, {{ now()->format('d F Y') }}<br>
                Wali Kelas
                <br><br><br><br>
                <strong>{{ $classroom->homeroomTeacher?->name ?? 'Wali Kelas' }}</strong>
            </td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center; padding-top: 20px;">
                Mengetahui,<br>
                Kepala Sekolah
                <br><br><br><br>
                <strong>{{ $schoolProfile?->headmaster_name ?? 'Kepala Sekolah' }}</strong><br>
                NIP. {{ $schoolProfile?->headmaster_nip ?? '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
