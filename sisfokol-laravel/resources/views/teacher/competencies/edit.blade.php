@extends('layouts.app')

@section('title', 'Edit TP')
@section('page-title', 'Edit Tujuan Pembelajaran')

@section('content')
    <div class="card">
        <form action="{{ route('teacher.competencies.update', $competency) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Tahun Pelajaran</label>
                    <select name="academic_year_id" class="form-control" required>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $competency->academic_year_id == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Mapel</label>
                    <select name="subject_id" class="form-control" required>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ $competency->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Fase</label>
                    <input type="text" name="phase" class="form-control" value="{{ old('phase', $competency->phase) }}" required>
                </div>
                <div class="form-group">
                    <label>Kode TP</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $competency->code) }}" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control" rows="4" required>{{ old('description', $competency->description) }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
