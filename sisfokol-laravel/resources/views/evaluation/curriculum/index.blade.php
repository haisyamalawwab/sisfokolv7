@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold mb-0">Kompetensi Kurikulum</h1>
        <a href="{{ route('evaluation.curriculum.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Tambah Kompetensi
        </a>
    </div>

<section class="content">
    <div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('evaluation.curriculum.index') }}" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <select name="mapel_id" class="form-select">
                        <option value="">-- Pilih Mata Pelajaran (Semua) --</option>
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->id }}" {{ $mapelId == $mapel->id ? 'selected' : '' }}>
                                {{ $mapel->nama }} ({{ $mapel->kode }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Daftar Capaian / Kompetensi</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%">Kode</th>
                            <th style="width: 25%">Mata Pelajaran</th>
                            <th style="width: 15%">Fase</th>
                            <th style="width: 35%">Deskripsi</th>
                            <th style="width: 10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($competencies as $c)
                            <tr>
                                <td class="fw-bold text-primary">{{ $c->code }}</td>
                                <td>{{ $c->subject?->name ?? $c->subject_id }}</td>
                                <td><span class="badge bg-secondary">Fase {{ $c->phase }}</span></td>
                                <td>{{ $c->description }}</td>
                                <td>-</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Tidak ada kompetensi kurikulum yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
