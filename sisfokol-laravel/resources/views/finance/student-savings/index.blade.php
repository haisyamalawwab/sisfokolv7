@extends('layouts.app')

@section('title', 'Tabungan Siswa')
@section('page-title', 'Data Tabungan Siswa')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Transaksi Tabungan</h3>
            <a href="{{ route('finance.student-savings.create') }}" class="btn btn-primary btn-sm float-right">Tambah</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Saldo</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($savings as $saving)
                        <tr>
                            <td>{{ $saving->date?->format('d-m-Y') }}</td>
                            <td>{{ $saving->student?->name }}</td>
                            <td>{!! $saving->is_debit ? '<span class="badge badge-success">Debet</span>' : '<span class="badge badge-warning">Kredit</span>' !!}</td>
                            <td>Rp {{ number_format($saving->amount, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($saving->balance, 0, ',', '.') }}</td>
                            <td>
                                <form action="{{ route('finance.student-savings.destroy', $saving) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $savings->links() }}
        </div>
    </div>
@endsection
