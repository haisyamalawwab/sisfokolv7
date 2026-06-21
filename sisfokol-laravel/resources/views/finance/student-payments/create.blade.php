@extends('layouts.app')

@section('title', 'Tambah Pembayaran')
@section('page-title', 'Tambah Pembayaran Siswa')

@section('content')
    <div class="card">
        <form action="{{ route('finance.student-payments.store') }}" method="POST">
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
                    <label>Tanggal Bayar</label>
                    <input type="date" name="payment_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Total</label>
                    <input type="number" name="total" class="form-control" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Metode</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="cash">Tunai</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="note" class="form-control"></textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
