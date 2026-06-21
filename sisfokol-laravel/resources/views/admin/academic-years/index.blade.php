@extends('layouts.app')

@section('title', 'Tahun Pelajaran')
@section('page-title', 'Data Tahun Pelajaran')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Tahun Pelajaran</h3>
            <a href="{{ route('admin.academic-years.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Aktif</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($academicYears as $year)
                        <tr>
                            <td>{{ $year->name }}</td>
                            <td>{{ $year->start_date?->format('d-m-Y') }}</td>
                            <td>{{ $year->end_date?->format('d-m-Y') }}</td>
                            <td>{!! $year->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Tidak</span>' !!}</td>
                            <td>
                                <a href="{{ route('admin.academic-years.edit', $year) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('admin.academic-years.destroy', $year) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $academicYears->links() }}
        </div>
    </div>
@endsection
