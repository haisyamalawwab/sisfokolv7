@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col">
            <h1 class="h3 fw-bold mb-0">Tambah Kompetensi Kurikulum</h1>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Form Baru</h5>
                </div>
                <form method="POST" action="{{ route('evaluation.curriculum.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label>Mata Pelajaran</label>
                            <select name="subject_id" class="form-control @error('subject_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($mapels as $mapel)
                                    <option value="{{ $mapel->id }}" {{ old('subject_id') == $mapel->id ? 'selected' : '' }}>
                                        {{ $mapel->nama }} ({{ $mapel->kode }})
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Fase (e.g. A, B, C, D, E, F)</label>
                            <input type="text" name="phase" class="form-control @error('phase') is-invalid @enderror" value="{{ old('phase') }}" placeholder="Fase E" required>
                            @error('phase')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Kode Kompetensi (e.g. CP-01)</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="CP-01" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Deskripsi Kompetensi</label>
                            <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Menjelaskan konsep matematika..." required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('evaluation.curriculum.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
