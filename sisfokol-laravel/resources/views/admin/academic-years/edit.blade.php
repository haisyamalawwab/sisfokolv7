@extends('layouts.app')

@section('title', 'Edit Tahun Pelajaran')
@section('page-title', 'Edit Tahun Pelajaran')

@section('content')
    <div class="card">
        <form action="{{ route('admin.academic-years.update', $academicYear) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $academicYear->name) }}" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $academicYear->start_date?->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $academicYear->end_date?->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <div class="icheck-primary">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ $academicYear->is_active ? 'checked' : '' }}>
                        <label for="is_active">Aktif</label>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
