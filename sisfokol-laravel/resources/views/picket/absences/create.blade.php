@extends('layouts.app')

@section('title', 'Tambah Absensi')
@section('page-title', 'Tambah Absensi')

@section('content')
    <div class="card">
        <form action="{{ route('picket.absences.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>NIS/NIP</label>
                    <input type="text" name="code" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Jenis</label>
                    <select name="type" class="form-control" required>
                        <option value="sakit">Sakit</option>
                        <option value="ijin">Ijin</option>
                        <option value="alpha">Alpha</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="reason" class="form-control"></textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
