@extends('layouts.app')

@section('title', 'Dashboard Sarpras')
@section('page-title', 'Dashboard Sarana Prasarana')

@section('content')
    <div class="row">
        <x-info-box title="Total Pegawai" :value="$stats['total_employees']" icon="fa-users" color="primary" />
    </div>
@endsection
