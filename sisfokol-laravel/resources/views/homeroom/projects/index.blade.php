@extends('layouts.app')

@section('title', 'Proyek')
@section('page-title', 'Kurikulum Merdeka - Proyek Kelas')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Proyek - {{ $classroom?->name ?? '-' }}</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama Proyek</th>
                        <th>Periode</th>
                        <th>Deskripsi</th>
                        <th>TP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr>
                            <td>{{ $project->name }}</td>
                            <td>{{ $project->start_date?->format('d/m/Y') }} - {{ $project->end_date?->format('d/m/Y') }}</td>
                            <td>{{ $project->description }}</td>
                            <td>
                                @foreach ($project->details as $detail)
                                    <span class="badge badge-info">{{ $detail->competency?->code }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $projects->links() }}
        </div>
    </div>
@endsection
