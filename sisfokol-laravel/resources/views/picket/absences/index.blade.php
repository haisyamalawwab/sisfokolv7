@extends('layouts.app')

@section('title', 'Absensi')
@section('page-title', 'Data Absensi')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Absensi</h3>
            <a href="{{ route('picket.absences.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($absences as $absence)
                        <tr>
                            <td>{{ $absence->date?->format('d-m-Y') }}</td>
                            <td>{{ $absence->user?->display_name }}</td>
                            <td>{{ ucfirst($absence->type) }}</td>
                            <td>{{ $absence->reason }}</td>
                            <td>
                                <form action="{{ route('picket.absences.destroy', $absence) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $absences->links() }}
        </div>
    </div>
@endsection
