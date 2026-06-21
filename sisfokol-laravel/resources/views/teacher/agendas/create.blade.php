@extends('layouts.app')

@section('title', 'Tambah Jurnal')
@section('page-title', 'Tambah Jurnal Mengajar')

@section('content')
    <div class="card">
        <form action="{{ route('teacher.agendas.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Jadwal</label>
                    <select name="schedule_id" class="form-control" required>
                        @foreach ($schedules as $schedule)
                            <option value="{{ $schedule->id }}">{{ $schedule->day?->name }} {{ $schedule->timeSlot?->name }} - {{ $schedule->classroom?->name }} - {{ $schedule->subject?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Topik</label>
                    <input type="text" name="topic" class="form-control">
                </div>
                <div class="form-group">
                    <label>Materi</label>
                    <textarea name="material" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label>Jumlah Siswa</label>
                    <input type="number" name="student_count" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="notes" class="form-control"></textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
