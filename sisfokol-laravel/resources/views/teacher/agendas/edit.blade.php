@extends('layouts.app')

@section('title', 'Edit Jurnal')
@section('page-title', 'Edit Jurnal Mengajar')

@section('content')
    <div class="card">
        <form action="{{ route('teacher.agendas.update', $agenda) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Jadwal</label>
                    <select name="schedule_id" class="form-control" required>
                        @foreach ($schedules as $schedule)
                            <option value="{{ $schedule->id }}" {{ $agenda->schedule_id == $schedule->id ? 'selected' : '' }}>{{ $schedule->day?->name }} {{ $schedule->timeSlot?->name }} - {{ $schedule->classroom?->name }} - {{ $schedule->subject?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ old('date', $agenda->date?->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label>Topik</label>
                    <input type="text" name="topic" class="form-control" value="{{ old('topic', $agenda->topic) }}">
                </div>
                <div class="form-group">
                    <label>Materi</label>
                    <textarea name="material" class="form-control">{{ old('material', $agenda->material) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Jumlah Siswa</label>
                    <input type="number" name="student_count" class="form-control" value="{{ old('student_count', $agenda->student_count) }}">
                </div>
                <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="notes" class="form-control">{{ old('notes', $agenda->notes) }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
