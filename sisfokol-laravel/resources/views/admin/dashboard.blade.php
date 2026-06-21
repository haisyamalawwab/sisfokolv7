@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Administrator')

@section('content')
    <div class="row">
        <x-info-box title="Pegawai/Guru" :value="$stats['total_employees']" icon="fa-user-secret" color="primary" />
        <x-info-box title="Siswa" :value="$stats['total_students']" icon="fa-users" color="success" />
        <x-info-box title="Kelas" :value="$stats['total_classrooms']" icon="fa-building" color="warning" />
        <x-info-box title="Mapel" :value="$stats['total_subjects']" icon="fa-book" color="info" />
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">History Login Terakhir</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Nama</th>
                                <th>Posisi</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stats['last_logins'] as $log)
                                <tr>
                                    <td>{{ $log->logged_in_at?->format('d-m-Y H:i') }}</td>
                                    <td>{{ $log->user_name }}</td>
                                    <td>{{ $log->position }}</td>
                                    <td>{{ $log->ip_address }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data login</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
