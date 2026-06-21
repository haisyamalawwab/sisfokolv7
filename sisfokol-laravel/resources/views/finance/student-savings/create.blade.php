@extends('layouts.app')

@section('title', 'Tambah Tabungan')
@section('page-title', 'Entri Tabungan Siswa')

@section('content')
    <div class="card">
        <form action="{{ route('finance.student-savings.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Siswa</label>
                    <select name="student_id" class="form-control" required>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->nis }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Jenis</label>
                    <select name="is_debit" class="form-control" required>
                        <option value="1">Debet (Menabung)</option>
                        <option value="0">Kredit (Ambil)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah</label>
                    <input type="number" name="amount" class="form-control" step="0.01" required>
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
