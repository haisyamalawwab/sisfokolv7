@extends('layouts.app')

@section('title', 'Waktu Presensi')
@section('page-title', 'Pengaturan Waktu Presensi')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Waktu Presensi</h3>
            <a href="{{ route('admin.attendance-times.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                        <th>Tapel</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendanceTimes as $time)
                        <tr>
                            <td>{{ $time->type == 'in' ? 'Hadir' : 'Pulang' }}</td>
                            <td>{{ $time->start_time?->format('H:i') }}</td>
                            <td>{{ $time->end_time?->format('H:i') }}</td>
                            <td>{{ $time->academicYear?->name }}</td>
                            <td>{!! $time->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Nonaktif</span>' !!}</td>
                            <td>
                                <a href="{{ route('admin.attendance-times.edit', $time) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('admin.attendance-times.destroy', $time) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $attendanceTimes->links() }}
        </div>
    </div>
@endsection
