@extends('layouts.app')

@section('title', 'Pembayaran')
@section('page-title', 'Data Pembayaran Siswa')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Pembayaran</h3>
            <a href="{{ route('finance.student-payments.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No Nota</th>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $payment)
                        <tr>
                            <td>{{ $payment->invoice_number }}</td>
                            <td>{{ $payment->payment_date?->format('d-m-Y') }}</td>
                            <td>{{ $payment->student?->name }}</td>
                            <td>Rp {{ number_format($payment->total, 0, ',', '.') }}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td>
                                <a href="{{ route('finance.student-payments.show', $payment) }}" class="btn btn-info btn-sm">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $payments->links() }}
        </div>
    </div>
@endsection
