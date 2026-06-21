@extends('layouts.app')

@section('title', 'Pelanggaran')
@section('page-title', 'Data Pelanggaran Siswa')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Pelanggaran</h3>
            <a href="{{ route('counselor.violations.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Jenis</th>
                        <th>Poin</th>
                        <th>Pelapor</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($violations as $violation)
                        <tr>
                            <td>{{ $violation->date?->format('d-m-Y') }}</td>
                            <td>{{ $violation->student?->name }}</td>
                            <td>{{ $violation->violationPoint?->name }}</td>
                            <td>{{ $violation->violationPoint?->point }}</td>
                            <td>{{ $violation->reporter?->name }}</td>
                            <td>
                                <a href="{{ route('counselor.violations.edit', $violation) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('counselor.violations.destroy', $violation) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $violations->links() }}
        </div>
    </div>
@endsection
