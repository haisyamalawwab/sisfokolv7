@extends('layouts.app')

@section('title', 'Mapel')
@section('page-title', 'Data Mata Pelajaran')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Mapel</h3>
            <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <th>Fase</th>
                        <th>Tapel</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subjects as $subject)
                        <tr>
                            <td>{{ $subject->code }}</td>
                            <td>{{ $subject->name }}</td>
                            <td>{{ $subject->subjectType?->name }}</td>
                            <td>{{ $subject->phase }}</td>
                            <td>{{ $subject->academicYear?->name }}</td>
                            <td>
                                <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $subjects->links() }}
        </div>
    </div>
@endsection
