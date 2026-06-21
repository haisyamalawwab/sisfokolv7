@extends('layouts.app')

@section('title', 'Dashboard Guru')
@section('page-title', 'Dashboard Guru Mapel')

@section('content')
    <div class="row">
        <x-info-box title="Mapel Diampu" :value="$stats['total_subjects']" icon="fa-book" color="primary" />
        <x-info-box title="Jadwal Mengajar" :value="$stats['total_schedules']" icon="fa-clock" color="success" />
        <x-info-box title="Kelas Diampu" :value="$stats['total_classrooms']" icon="fa-building" color="warning" />
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Jadwal Hari Ini</h3>
                </div>
                <div class="card-body">
                    @if ($stats['today_schedules']->isEmpty())
                        <p class="text-muted">Tidak ada jadwal hari ini.</p>
                    @else
                        <ul class="list-group">
                            @foreach ($stats['today_schedules'] as $schedule)
                                <li class="list-group-item">
                                    {{ $schedule->timeSlot?->name }} - {{ $schedule->classroom?->name }} - {{ $schedule->subject?->name }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
