@extends('layouts.app')

@section('title', 'Item Pembayaran')
@section('page-title', 'Data Item Pembayaran')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Item Pembayaran</h3>
            <a href="{{ route('finance.payment-items.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kode</th>
                        <th>Nominal</th>
                        <th>Frekuensi</th>
                        <th>Tapel</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($paymentItems as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->code }}</td>
                            <td>Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                            <td>{{ $item->frequency }}</td>
                            <td>{{ $item->academicYear?->name }}</td>
                            <td>{!! $item->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Nonaktif</span>' !!}</td>
                            <td>
                                <a href="{{ route('finance.payment-items.edit', $item) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('finance.payment-items.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $paymentItems->links() }}
        </div>
    </div>
@endsection
