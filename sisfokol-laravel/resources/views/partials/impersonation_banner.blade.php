@php
    $imp = app(\App\Modules\Auth\Services\ImpersonationService::class);
@endphp
@if($imp->isImpersonating())
    @php
        $original = \App\Models\User::find(session('impersonated_by'));
    @endphp
    <div class="alert alert-danger d-flex justify-content-between align-items-center mb-0 rounded-0" style="z-index: 9999; position: relative; background: #991b1b; color: white; border: none; padding: 12px 24px;">
        <div>
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Anda sedang login sebagai <strong>{{ auth()->user()->nama }}</strong>
            (impersonated oleh {{ $original?->nama ?? 'SuperAdmin' }}).
            <span class="badge badge-warning ml-2">Aksi Sensitif Diblokir</span>
        </div>
        <form method="POST" action="{{ route('impersonate.stop') }}">
            @csrf
            <button class="btn btn-sm btn-light" type="submit">
                <i class="fas fa-undo mr-1"></i> Kembali ke Akun Saya
            </button>
        </form>
    </div>
@endif
