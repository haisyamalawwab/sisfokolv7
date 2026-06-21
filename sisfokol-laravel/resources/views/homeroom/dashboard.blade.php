@extends('layouts.app')

@section('title', 'Dashboard Wali Kelas')
@section('page-title', 'Dashboard Wali Kelas')

@section('content')
    <div class="row">
        <x-info-box title="Nama" :value="$stats['name']" icon="fa-user" color="primary" />
        <x-info-box title="Kelas" :value="$stats['classroom']" icon="fa-building" color="success" />
        <x-info-box title="Jumlah Siswa" :value="$stats['total_students']" icon="fa-users" color="warning" />
    </div>
@endsection
