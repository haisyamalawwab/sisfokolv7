@extends('layouts.app')

@section('title', 'Tambah Mapel')
@section('page-title', 'Tambah Mata Pelajaran')

@section('content')
    <div class="card">
        <form action="{{ route('admin.subjects.store') }}" method="POST">
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
                    <label>Jenis Mapel</label>
                    <select name="subject_type_id" class="form-control">
                        <option value="">- Pilih -</option>
                        @foreach ($subjectTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Kode</label>
                    <input type="text" name="code" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Fase</label>
                    <input type="text" name="phase" class="form-control" placeholder="E / F">
                </div>
                <div class="form-group">
                    <div class="icheck-primary">
                        <input type="checkbox" id="is_exam" name="is_exam" value="1">
                        <label for="is_exam">Ujian</label>
                    </div>
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
