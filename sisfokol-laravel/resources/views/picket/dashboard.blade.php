@extends('layouts.app')

@section('title', 'Dashboard Piket')
@section('page-title', 'Dashboard Petugas Piket')

@section('content')
    <div class="row">
        <x-info-box title="Total Siswa" :value="$stats['total_students']" icon="fa-users" color="primary" />
        <x-info-box title="Total Pegawai" :value="$stats['total_employees']" icon="fa-user-secret" color="success" />
    </div>
@endsection
