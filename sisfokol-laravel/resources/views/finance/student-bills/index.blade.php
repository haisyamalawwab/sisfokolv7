@extends('layouts.app')

@section('title', 'Tagihan Siswa')
@section('page-title', 'Data Tagihan Siswa')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Tagihan</h3>
            <a href="{{ route('finance.student-bills.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Item</th>
                        <th>Periode</th>
                        <th>Jumlah</th>
                        <th>Dibayar</th>
                        <th>Kekurangan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bills as $bill)
                        <tr>
                            <td>{{ $bill->student?->name }}</td>
                            <td>{{ $bill->paymentItem?->name }}</td>
                            <td>{{ $bill->period }}</td>
                            <td>Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($bill->paid, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($bill->remaining, 0, ',', '.') }}</td>
                            <td>{!! ucfirst($bill->status) !!}</td>
                            <td>
                                <form action="{{ route('finance.student-bills.destroy', $bill) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $bills->links() }}
        </div>
    </div>
@endsection
