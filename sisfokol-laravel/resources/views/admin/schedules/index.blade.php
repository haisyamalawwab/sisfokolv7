@extends('layouts.app')

@section('title', 'Jadwal')
@section('page-title', 'Data Jadwal Pelajaran')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Jadwal</h3>
            <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Hari</th>
                        <th>Jam</th>
                        <th>Kelas</th>
                        <th>Mapel</th>
                        <th>Guru</th>
                        <th>Ruang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->day?->name }}</td>
                            <td>{{ $schedule->timeSlot?->name }}</td>
                            <td>{{ $schedule->classroom?->name }}</td>
                            <td>{{ $schedule->subject?->name }}</td>
                            <td>{{ $schedule->teacher?->name }}</td>
                            <td>{{ $schedule->room?->name }}</td>
                            <td>
                                <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $schedules->links() }}
        </div>
    </div>
@endsection
