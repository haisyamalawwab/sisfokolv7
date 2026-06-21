@extends('layouts.app')

@section('title', 'Tambah Kelas')
@section('page-title', 'Tambah Kelas')

@section('content')
    <div class="card">
        <form action="{{ route('admin.classrooms.store') }}" method="POST">
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
                    <label>Nama Kelas</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tingkat</label>
                    <input type="text" name="level" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Jurusan</label>
                    <input type="text" name="major" class="form-control">
                </div>
                <div class="form-group">
                    <label>Kapasitas</label>
                    <input type="number" name="capacity" class="form-control" value="30" required>
                </div>
                <div class="form-group">
                    <label>Wali Kelas</label>
                    <select name="homeroom_teacher_id" class="form-control">
                        <option value="">- Pilih -</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
