@extends('layouts.app')

@section('title', 'Tambah Item Pembayaran')
@section('page-title', 'Tambah Item Pembayaran')

@section('content')
    <div class="card">
        <form action="{{ route('finance.payment-items.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Tahun Pelajaran</label>
                    <select name="academic_year_id" class="form-control" required>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Kode</label>
                    <input type="text" name="code" class="form-control">
                </div>
                <div class="form-group">
                    <label>Nominal</label>
                    <input type="number" name="amount" class="form-control" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Frekuensi</label>
                    <select name="frequency" class="form-control" required>
                        <option value="once">Sekali</option>
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>
                <div class="form-group">
                    <div class="icheck-primary">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label for="is_active">Aktif</label>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
