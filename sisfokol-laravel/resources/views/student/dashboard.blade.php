@extends('layouts.app')

@section('title', 'Dashboard Siswa')
@section('page-title', 'Dashboard Siswa')

@section('content')
    <div class="row">
        <x-info-box title="Nama" :value="$stats['name']" icon="fa-user" color="primary" />
        <x-info-box title="Kelas" :value="$stats['classroom']" icon="fa-building" color="success" />
        <x-info-box title="Tapel" :value="$stats['academic_year']" icon="fa-calendar" color="warning" />
    </div>
@endsection
