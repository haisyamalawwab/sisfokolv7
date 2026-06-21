@extends('layouts.app')

@section('title', 'Tujuan Pembelajaran')
@section('page-title', 'Kurikulum Merdeka - Tujuan Pembelajaran')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar TP</h3>
            <a href="{{ route('teacher.competencies.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Mapel</th>
                        <th>Fase</th>
                        <th>Kode</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($competencies as $competency)
                        <tr>
                            <td>{{ $competency->subject?->name }}</td>
                            <td>{{ $competency->phase }}</td>
                            <td>{{ $competency->code }}</td>
                            <td>{{ Str::limit($competency->description, 80) }}</td>
                            <td>
                                <a href="{{ route('teacher.competencies.edit', $competency) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('teacher.competencies.destroy', $competency) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $competencies->links() }}
        </div>
    </div>
@endsection
