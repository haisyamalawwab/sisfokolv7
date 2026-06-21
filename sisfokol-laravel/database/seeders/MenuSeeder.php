<?php

namespace Database\Seeders;

use App\Modules\Auth\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['kode' => 'dashboard',        'label' => 'Dashboard',         'route' => 'dashboard',         'urutan' => 1,   'group' => 'Utama',       'permission_required' => 'dashboard.view',     'is_system' => true,  'icon' => 'fas fa-home'],
            // Tenancy (super admin)
            ['kode' => 'tenancy.tenants',  'label' => 'Tenants',           'route' => 'tenants.index',     'urutan' => 10,  'group' => 'Platform',    'permission_required' => 'tenant.view',        'is_system' => true,  'icon' => 'fas fa-building'],
            ['kode' => 'tenancy.branches', 'label' => 'Branches',          'route' => 'branches.index',    'urutan' => 11,  'group' => 'Platform',    'permission_required' => 'tenant.view',        'is_system' => true,  'icon' => 'fas fa-sitemap'],
            // Auth
            ['kode' => 'auth.users',       'label' => 'Pengguna',          'route' => 'rbac.users',        'urutan' => 20,  'group' => 'Manajemen',   'permission_required' => 'user.manage',        'is_system' => true,  'icon' => 'fas fa-users'],
            ['kode' => 'auth.rbac',        'label' => 'RBAC Builder',      'route' => 'rbac.index',        'urutan' => 21,  'group' => 'Manajemen',   'permission_required' => 'rbac.manage',        'is_system' => true,  'icon' => 'fas fa-shield-alt'],
            ['kode' => 'auth.audit',       'label' => 'Audit Log',         'route' => 'audit.index',       'urutan' => 22,  'group' => 'Manajemen',   'permission_required' => 'audit.view',         'is_system' => true,  'icon' => 'fas fa-history'],
            ['kode' => 'auth.plugins',     'label' => 'Plugin',            'route' => 'plugins.index',     'urutan' => 23,  'group' => 'Manajemen',   'permission_required' => 'plugin.activate',    'is_system' => true,  'icon' => 'fas fa-puzzle-piece'],
            // Academic (Epic 5)
            ['kode' => 'academic.siswa',   'label' => 'Siswa',             'route' => 'siswa.index',       'urutan' => 30,  'group' => 'Akademik',    'permission_required' => 'siswa.view',         'is_system' => true,  'icon' => 'fas fa-user-graduate'],
            ['kode' => 'academic.guru',    'label' => 'Guru',              'route' => 'guru.index',        'urutan' => 31,  'group' => 'Akademik',    'permission_required' => 'guru.view',          'is_system' => true,  'icon' => 'fas fa-user-tie'],
            ['kode' => 'academic.kelas',   'label' => 'Kelas',             'route' => 'kelas.index',       'urutan' => 32,  'group' => 'Akademik',    'permission_required' => 'kelas.view',         'is_system' => true,  'icon' => 'fas fa-school'],
            ['kode' => 'academic.mapel',   'label' => 'Mapel',             'route' => 'mapel.index',       'urutan' => 33,  'group' => 'Akademik',    'permission_required' => 'mapel.view',         'is_system' => true,  'icon' => 'fas fa-book'],
            ['kode' => 'academic.jadwal',  'label' => 'Jadwal',            'route' => 'jadwal.index',      'urutan' => 34,  'group' => 'Akademik',    'permission_required' => 'jadwal.view',        'is_system' => true,  'icon' => 'fas fa-calendar-alt'],
            // Finance (Epic 7)
            ['kode' => 'finance.tagihan',  'label' => 'Tagihan Siswa',     'route' => 'tagihan.index',     'urutan' => 40,  'group' => 'Keuangan',    'permission_required' => 'tagihan.view',       'is_system' => true,  'icon' => 'fas fa-file-invoice-dollar'],
            ['kode' => 'finance.bayar',    'label' => 'Pembayaran',        'route' => 'pembayaran.index',  'urutan' => 41,  'group' => 'Keuangan',    'permission_required' => 'pembayaran.view',    'is_system' => true,  'icon' => 'fas fa-cash-register'],
            ['kode' => 'finance.tabungan', 'label' => 'Tabungan',          'route' => 'tabungan.index',    'urutan' => 42,  'group' => 'Keuangan',    'permission_required' => 'tabungan.view',      'is_system' => true,  'icon' => 'fas fa-piggy-bank'],
            // Presence (Epic 8)
            ['kode' => 'presence.presensi','label' => 'Presensi',          'route' => 'presensi.index',    'urutan' => 50,  'group' => 'Kehadiran',   'permission_required' => 'presensi.view',      'is_system' => true,  'icon' => 'fas fa-user-check'],
            ['kode' => 'presence.absensi', 'label' => 'Absensi',           'route' => 'absensi.index',     'urutan' => 51,  'group' => 'Kehadiran',   'permission_required' => 'absensi.view',       'is_system' => true,  'icon' => 'fas fa-user-clock'],
            // Evaluation (Epic 6)
            ['kode' => 'evaluation.rapor', 'label' => 'Rapor',             'route' => 'raport.index',      'urutan' => 60,  'group' => 'Evaluasi',    'permission_required' => 'raport.view',        'is_system' => true,  'icon' => 'fas fa-file-alt'],
        ];

        foreach ($menus as $m) {
            Menu::firstOrCreate(['kode' => $m['kode']], array_merge($m, ['aktif' => true]));
        }
    }
}
