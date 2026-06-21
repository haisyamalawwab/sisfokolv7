@extends('layouts.app')

@section('title', 'Dashboard Bendahara')
@section('page-title', 'Dashboard Bendahara')

@section('content')
    <div class="row">
        <x-info-box title="Total Siswa" :value="$stats['total_students']" icon="fa-users" color="primary" />
        <x-info-box title="User Aktif" :value="$stats['total_active_users']" icon="fa-user-check" color="success" />
    </div>
@endsection
