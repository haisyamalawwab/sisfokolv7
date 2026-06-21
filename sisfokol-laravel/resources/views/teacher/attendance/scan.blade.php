@extends('layouts.app')

@section('title', 'Scan Presensi')
@section('page-title', 'Scan Presensi QR')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Scan Hadir</h3>
                </div>
                <form action="{{ route('teacher.attendance.scan.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="in">
                    <div class="card-body">
                        <div class="form-group">
                            <label>NIS/NIP</label>
                            <input type="text" name="code" class="form-control" autofocus required>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success btn-block">HADIR</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Scan Pulang</h3>
                </div>
                <form action="{{ route('teacher.attendance.scan.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="out">
                    <div class="card-body">
                        <div class="form-group">
                            <label>NIS/NIP</label>
                            <input type="text" name="code" class="form-control" required>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block">PULANG</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
