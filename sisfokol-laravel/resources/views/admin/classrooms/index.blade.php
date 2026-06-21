@extends('layouts.app')

@section('title', 'Kelas')
@section('page-title', 'Data Kelas')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Kelas</h3>
            <a href="{{ route('admin.classrooms.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Tingkat</th>
                        <th>Jurusan</th>
                        <th>Tapel</th>
                        <th>Wali Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($classrooms as $classroom)
                        <tr>
                            <td>{{ $classroom->name }}</td>
                            <td>{{ $classroom->level }}</td>
                            <td>{{ $classroom->major }}</td>
                            <td>{{ $classroom->academicYear?->name }}</td>
                            <td>{{ $classroom->homeroomTeacher?->name }}</td>
                            <td>
                                <a href="{{ route('admin.classrooms.edit', $classroom) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('admin.classrooms.destroy', $classroom) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $classrooms->links() }}
        </div>
    </div>
@endsection
