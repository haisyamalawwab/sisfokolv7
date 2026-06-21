@extends('layouts.app')

@section('title', 'Edit Mapel')
@section('page-title', 'Edit Mata Pelajaran')

@section('content')
    <div class="card">
        <form action="{{ route('admin.subjects.update', $subject) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Tahun Pelajaran</label>
                    <select name="academic_year_id" class="form-control" required>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $subject->academic_year_id == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Jenis Mapel</label>
                    <select name="subject_type_id" class="form-control">
                        <option value="">- Pilih -</option>
                        @foreach ($subjectTypes as $type)
                            <option value="{{ $type->id }}" {{ $subject->subject_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Kode</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $subject->code) }}" required>
                </div>
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $subject->name) }}" required>
                </div>
                <div class="form-group">
                    <label>Fase</label>
                    <input type="text" name="phase" class="form-control" value="{{ old('phase', $subject->phase) }}">
                </div>
                <div class="form-group">
                    <div class="icheck-primary">
                        <input type="checkbox" id="is_exam" name="is_exam" value="1" {{ $subject->is_exam ? 'checked' : '' }}>
                        <label for="is_exam">Ujian</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control">{{ old('description', $subject->description) }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
