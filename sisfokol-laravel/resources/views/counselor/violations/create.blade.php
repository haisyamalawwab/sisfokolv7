@extends('layouts.app')

@section('title', 'Tambah Pelanggaran')
@section('page-title', 'Tambah Pelanggaran Siswa')

@section('content')
    <div class="card">
        <form action="{{ route('counselor.violations.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Tahun Pelajaran</label>
                    <select name="academic_year_id" class="form-control" required>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Siswa</label>
                    <select name="student_id" class="form-control" required>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->nis }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Pelanggaran</label>
                    <select name="violation_point_id" class="form-control" required>
                        @foreach ($violationPoints as $point)
                            <option value="{{ $point->id }}">{{ $point->violationType?->name }} - {{ $point->name }} ({{ $point->point }} poin)</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Pelapor</label>
                    <select name="employee_id" class="form-control">
                        <option value="">- Pilih -</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
