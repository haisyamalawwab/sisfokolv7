@extends('layouts.app')

@section('title', 'Asesmen Formatif')
@section('page-title', 'Kurikulum Merdeka - Asesmen Formatif')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Asesmen Formatif</h3>
            <a href="{{ route('teacher.formative-assessments.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Mapel</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assessments as $assessment)
                        <tr>
                            <td>{{ $assessment->assessment_date?->format('d-m-Y') }}</td>
                            <td>{{ $assessment->name }}</td>
                            <td>{{ $assessment->subject?->name }}</td>
                            <td>{{ $assessment->classroom?->name }}</td>
                            <td>
                                <a href="{{ route('teacher.formative-assessments.show', $assessment) }}" class="btn btn-info btn-sm">Nilai</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $assessments->links() }}
        </div>
    </div>
@endsection
