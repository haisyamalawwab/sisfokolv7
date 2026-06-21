@extends('layouts.app')

@section('title', 'Izin')
@section('page-title', 'Data Izin Masuk/Pulang')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Izin</h3>
            <a href="{{ route('picket.permits.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <th>Jam</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permits as $permit)
                        <tr>
                            <td>{{ $permit->date?->format('d-m-Y') }}</td>
                            <td>{{ $permit->user?->display_name }}</td>
                            <td>{{ $permit->type == 'in' ? 'Masuk' : 'Pulang' }}</td>
                            <td>{{ $permit->time?->format('H:i') }}</td>
                            <td>{{ $permit->reason }}</td>
                            <td>{!! ucfirst($permit->status) !!}</td>
                            <td>
                                @if ($permit->status == 'pending')
                                    <form action="{{ route('picket.permits.approve', $permit) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-success btn-sm">Setujui</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $permits->links() }}
        </div>
    </div>
@endsection
