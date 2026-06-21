@extends('layouts.app')

@section('title', 'Prestasi')
@section('page-title', 'Data Prestasi Siswa')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Prestasi</h3>
            <a href="{{ route('counselor.achievements.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Judul</th>
                        <th>Jenis</th>
                        <th>Tingkat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($achievements as $achievement)
                        <tr>
                            <td>{{ $achievement->date?->format('d-m-Y') }}</td>
                            <td>{{ $achievement->student?->name }}</td>
                            <td>{{ $achievement->title }}</td>
                            <td>{{ $achievement->achievementType?->name }}</td>
                            <td>{{ $achievement->level }}</td>
                            <td>
                                <a href="{{ route('counselor.achievements.edit', $achievement) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('counselor.achievements.destroy', $achievement) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $achievements->links() }}
        </div>
    </div>
@endsection
