@extends('layouts.app')

@section('title', 'Tambah Asesmen Formatif')
@section('page-title', 'Tambah Asesmen Formatif')

@section('content')
    <div class="card">
        <form action="{{ route('teacher.formative-assessments.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Tahun Pelajaran</label>
                    <select name="academic_year_id" class="form-control" required>
                        @foreach (\App\Models\AcademicYear::all() as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Mapel</label>
                    <select name="subject_id" class="form-control" required>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Kelas</label>
                    <select name="classroom_id" class="form-control" required>
                        @foreach ($classrooms as $classroom)
                            <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Nama Asesmen</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="assessment_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
