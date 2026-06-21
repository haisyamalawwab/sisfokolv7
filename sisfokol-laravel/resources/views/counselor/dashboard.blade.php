@extends('layouts.app')

@section('title', 'Dashboard Guru BK')
@section('page-title', 'Dashboard Guru BK')

@section('content')
    <div class="row">
        <x-info-box title="Total Siswa" :value="$stats['total_students']" icon="fa-users" color="primary" />
    </div>
@endsection
