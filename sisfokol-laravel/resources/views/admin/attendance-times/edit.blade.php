@extends('layouts.app')

@section('title', 'Edit Waktu Presensi')
@section('page-title', 'Edit Waktu Presensi')

@section('content')
    <div class="card">
        <form action="{{ route('admin.attendance-times.update', $attendanceTime) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Tahun Pelajaran</label>
                    <select name="academic_year_id" class="form-control" required>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $attendanceTime->academic_year_id == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Jenis</label>
                    <select name="type" class="form-control" required>
                        <option value="in" {{ $attendanceTime->type == 'in' ? 'selected' : '' }}>Hadir</option>
                        <option value="out" {{ $attendanceTime->type == 'out' ? 'selected' : '' }}>Pulang</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jam Mulai</label>
                    <input type="time" name="start_time" class="form-control" value="{{ old('start_time', $attendanceTime->start_time?->format('H:i')) }}" required>
                </div>
                <div class="form-group">
                    <label>Jam Selesai</label>
                    <input type="time" name="end_time" class="form-control" value="{{ old('end_time', $attendanceTime->end_time?->format('H:i')) }}" required>
                </div>
                <div class="form-group">
                    <div class="icheck-primary">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ $attendanceTime->is_active ? 'checked' : '' }}>
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
