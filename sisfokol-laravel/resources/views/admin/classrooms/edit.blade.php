@extends('layouts.app')

@section('title', 'Edit Kelas')
@section('page-title', 'Edit Kelas')

@section('content')
    <div class="card">
        <form action="{{ route('admin.classrooms.update', $classroom) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Tahun Pelajaran</label>
                    <select name="academic_year_id" class="form-control" required>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $classroom->academic_year_id == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Nama Kelas</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $classroom->name) }}" required>
                </div>
                <div class="form-group">
                    <label>Tingkat</label>
                    <input type="text" name="level" class="form-control" value="{{ old('level', $classroom->level) }}" required>
                </div>
                <div class="form-group">
                    <label>Jurusan</label>
                    <input type="text" name="major" class="form-control" value="{{ old('major', $classroom->major) }}">
                </div>
                <div class="form-group">
                    <label>Kapasitas</label>
                    <input type="number" name="capacity" class="form-control" value="{{ old('capacity', $classroom->capacity) }}" required>
                </div>
                <div class="form-group">
                    <label>Wali Kelas</label>
                    <select name="homeroom_teacher_id" class="form-control">
                        <option value="">- Pilih -</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ $classroom->homeroom_teacher_id == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
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
