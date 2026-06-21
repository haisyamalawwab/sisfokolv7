@extends('layouts.app')

@section('title', 'Profil Sekolah')
@section('page-title', 'Profil Sekolah')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Profil Sekolah</h3>
        </div>
        <form action="{{ route('admin.school-profile.update', $profile) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Nama Sekolah</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $profile->name) }}" required>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="address" class="form-control">{{ old('address', $profile->address) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Kota</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $profile->city) }}">
                </div>
                <div class="form-group">
                    <label>Telepon</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $profile->phone) }}">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
