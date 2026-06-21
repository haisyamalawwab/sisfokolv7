@extends('layouts.app')

@section('title', 'Edit Jadwal')
@section('page-title', 'Edit Jadwal Pelajaran')

@section('content')
    <div class="card">
        <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Tahun Pelajaran</label>
                    <select name="academic_year_id" class="form-control" required>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $schedule->academic_year_id == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Kelas</label>
                    <select name="classroom_id" class="form-control" required>
                        @foreach ($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" {{ $schedule->classroom_id == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Mapel</label>
                    <select name="subject_id" class="form-control" required>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ $schedule->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Guru</label>
                    <select name="employee_id" class="form-control" required>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ $schedule->employee_id == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Ruang</label>
                    <select name="room_id" class="form-control">
                        <option value="">- Pilih -</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}" {{ $schedule->room_id == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Hari</label>
                    <select name="day_id" class="form-control" required>
                        @foreach ($days as $day)
                            <option value="{{ $day->id }}" {{ $schedule->day_id == $day->id ? 'selected' : '' }}>{{ $day->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Jam</label>
                    <select name="time_slot_id" class="form-control" required>
                        @foreach ($timeSlots as $slot)
                            <option value="{{ $slot->id }}" {{ $schedule->time_slot_id == $slot->id ? 'selected' : '' }}>{{ $slot->name }} ({{ $slot->start_time?->format('H:i') }} - {{ $slot->end_time?->format('H:i') }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Minggu</label>
                    <select name="week_type" class="form-control" required>
                        <option value="all" {{ $schedule->week_type == 'all' ? 'selected' : '' }}>Semua</option>
                        <option value="odd" {{ $schedule->week_type == 'odd' ? 'selected' : '' }}>Ganjil</option>
                        <option value="even" {{ $schedule->week_type == 'even' ? 'selected' : '' }}>Genap</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
