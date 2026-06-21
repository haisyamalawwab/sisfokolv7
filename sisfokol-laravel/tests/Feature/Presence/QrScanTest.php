<?php

namespace Tests\Feature\Presence;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceTime;
use App\Models\User;
use App\Modules\Academic\Models\Siswa;
use App\Modules\Presence\Services\QrScannerService;
use App\Modules\Tenancy\Models\Tenant;
use App\Support\TenantContext;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrScanTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant1;
    private Tenant $tenant2;
    private Siswa $siswa1;
    private QrScannerService $scanner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scanner = app(QrScannerService::class);

        $this->tenant1 = Tenant::create(['nama' => 'Sekolah A', 'npsn' => '10000001']);
        $this->tenant2 = Tenant::create(['nama' => 'Sekolah B', 'npsn' => '10000002']);

        // Create Siswa in Tenant 1
        app(TenantContext::class)->set($this->tenant1->id);
        $this->siswa1 = Siswa::factory()->create(['tenant_id' => $this->tenant1->id, 'status' => 'aktif']);

        // Setup active academic year and attendance times
        $academicYear = AcademicYear::create([
            'name' => '2025/2026',
            'is_active' => true,
        ]);

        AttendanceTime::create([
            'academic_year_id' => $academicYear->id,
            'type' => 'in',
            'start_time' => '06:30:00',
            'end_time' => '07:30:00',
            'is_active' => true,
        ]);

        AttendanceTime::create([
            'academic_year_id' => $academicYear->id,
            'type' => 'out',
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'is_active' => true,
        ]);

        app(TenantContext::class)->clear();
    }

    public function test_qr_scan_successfully_creates_attendance(): void
    {
        // Mock current time to 07:00 (present)
        Carbon::setTestNow(Carbon::parse('2026-06-21 07:00:00'));

        $attendance = $this->scanner->scan($this->siswa1->nis, $this->tenant1->id);

        $this->assertNotNull($attendance);
        $this->assertEquals('present', $attendance->status);
        $this->assertEquals('in', $attendance->type);
        $this->assertEquals($this->siswa1->id, $attendance->attendable_id);

        Carbon::setTestNow();
    }

    public function test_qr_scan_marks_as_late_if_after_threshold(): void
    {
        // Mock current time to 07:45 (late)
        Carbon::setTestNow(Carbon::parse('2026-06-21 07:45:00'));

        $attendance = $this->scanner->scan($this->siswa1->nis, $this->tenant1->id);

        $this->assertEquals('late', $attendance->status);

        Carbon::setTestNow();
    }

    public function test_qr_scan_prevents_duplicate_scans_on_same_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-21 07:00:00'));

        $this->scanner->scan($this->siswa1->nis, $this->tenant1->id);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Siswa sudah melakukan presensi masuk hari ini.');

        $this->scanner->scan($this->siswa1->nis, $this->tenant1->id);

        Carbon::setTestNow();
    }

    public function test_qr_scan_enforces_tenant_isolation(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-21 07:00:00'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Siswa tidak ditemukan atau tidak aktif.');

        // Attempting to scan for Siswa 1 under Tenant 2
        $this->scanner->scan($this->siswa1->nis, $this->tenant2->id);

        Carbon::setTestNow();
    }
}
