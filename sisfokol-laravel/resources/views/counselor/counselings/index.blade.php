@extends('layouts.app')

@section('title', 'Pembinaan')
@section('page-title', 'Data Pembinaan Siswa')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Pembinaan</h3>
            <a href="{{ route('counselor.counselings.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Jenis</th>
                        <th>Guru BK</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($counselings as $counseling)
                        <tr>
                            <td>{{ $counseling->date?->format('d-m-Y') }}</td>
                            <td>{{ $counseling->student?->name }}</td>
                            <td>{{ $counseling->counselingType?->name }}</td>
                            <td>{{ $counseling->counselor?->employee?->name }}</td>
                            <td>
                                <a href="{{ route('counselor.counselings.edit', $counseling) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('counselor.counselings.destroy', $counseling) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $counselings->links() }}
        </div>
    </div>
@endsection
