@extends('layouts.app')

@section('title', 'Dashboard Kepala Sekolah')
@section('page-title', 'Dashboard Kepala Sekolah')

@section('content')
    <div class="row">
        <x-info-box title="Pegawai" :value="$stats['total_employees']" icon="fa-user-secret" color="primary" />
        <x-info-box title="Siswa" :value="$stats['total_students']" icon="fa-users" color="success" />
        <x-info-box title="Kelas" :value="$stats['total_classrooms']" icon="fa-building" color="warning" />
        <x-info-box title="Mapel" :value="$stats['total_subjects']" icon="fa-book" color="info" />
    </div>
@endsection
