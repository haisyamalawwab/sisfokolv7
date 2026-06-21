@extends('layouts.app')

@section('title', 'Jurnal Mengajar')
@section('page-title', 'Jurnal Mengajar')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Jurnal</h3>
            <a href="{{ route('teacher.agendas.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jadwal</th>
                        <th>Topik</th>
                        <th>Jumlah Siswa</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($agendas as $agenda)
                        <tr>
                            <td>{{ $agenda->date?->format('d-m-Y') }}</td>
                            <td>{{ $agenda->schedule?->classroom?->name }} - {{ $agenda->schedule?->subject?->name }}</td>
                            <td>{{ $agenda->topic }}</td>
                            <td>{{ $agenda->student_count }}</td>
                            <td>
                                <a href="{{ route('teacher.agendas.edit', $agenda) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('teacher.agendas.destroy', $agenda) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $agendas->links() }}
        </div>
    </div>
@endsection
