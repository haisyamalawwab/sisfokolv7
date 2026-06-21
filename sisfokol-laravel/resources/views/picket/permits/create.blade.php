@extends('layouts.app')

@section('title', 'Tambah Izin')
@section('page-title', 'Tambah Izin Masuk/Pulang')

@section('content')
    <div class="card">
        <form action="{{ route('picket.permits.store') }}" method="POST">
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
                        <option value="in">Izin Masuk</option>
                        <option value="out">Izin Pulang</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jam</label>
                    <input type="time" name="time" class="form-control">
                </div>
                <div class="form-group">
                    <label>Alasan</label>
                    <textarea name="reason" class="form-control" required></textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
