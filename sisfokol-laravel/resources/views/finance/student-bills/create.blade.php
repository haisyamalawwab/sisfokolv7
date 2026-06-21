@extends('layouts.app')

@section('title', 'Tambah Tagihan')
@section('page-title', 'Tambah Tagihan Siswa')

@section('content')
    <div class="card">
        <form action="{{ route('finance.student-bills.store') }}" method="POST">
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
                    <label>Siswa</label>
                    <select name="student_id" class="form-control" required>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->nis }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Item Pembayaran</label>
                    <select name="payment_item_id" class="form-control" required>
                        @foreach ($paymentItems as $item)
                            <option value="{{ $item->id }}">{{ $item->name }} - Rp {{ number_format($item->amount, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Periode</label>
                    <input type="text" name="period" class="form-control" placeholder="Januari 2026 / Tahun 2026">
                </div>
                <div class="form-group">
                    <label>Jumlah</label>
                    <input type="number" name="amount" class="form-control" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Jatuh Tempo</label>
                    <input type="date" name="due_date" class="form-control">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
