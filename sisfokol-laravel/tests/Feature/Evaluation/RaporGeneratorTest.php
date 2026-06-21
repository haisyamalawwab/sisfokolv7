<?php

namespace Tests\Feature\Evaluation;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Attendance;
use App\Models\Absence;
use App\Models\Permit;
use App\Models\ReportNote;
use App\Models\Student;
use App\Models\StudentSemesterScore;
use App\Models\Subject;
use App\Models\User;
use App\Modules\Academic\Models\Semester;
use App\Modules\Academic\Models\Siswa;
use App\Modules\Tenancy\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaporGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Student $student;
    protected Siswa $siswa;
    protected Classroom $classroom;
    protected Subject $subject;
    protected AcademicYear $academicYear;
    protected Semester $semester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'nama' => 'Sekolah Test Rapor',
            'npsn' => '22222222',
            'domain' => 'rapor.school.test',
        ]);

        app(TenantContext::class)->set($this->tenant->id);

        $this->academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $tahunAjaran = \App\Modules\Academic\Models\TahunAjaran::create([
            'id' => $this->academicYear->id,
            'tenant_id' => $this->tenant->id,
            'nama' => '2026/2027',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2027-06-30',
            'aktif' => true,
        ]);

        $this->semester = Semester::create([
            'tenant_id' => $this->tenant->id,
            'tahun_ajaran_id' => $tahunAjaran->id,
            'nama' => 1,
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'aktif' => true,
        ]);

        $this->classroom = Classroom::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Kelas X-B',
            'level' => '10',
        ]);

        $this->subject = Subject::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Fisika',
            'code' => 'FIS',
        ]);

        $this->siswa = Siswa::create([
            'tenant_id' => $this->tenant->id,
            'nis' => '2026002',
            'nama' => 'Siti Aminah',
            'jenis_kelamin' => 'P',
            'status' => 'aktif',
        ]);

        $this->student = Student::create([
            'id' => $this->siswa->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroom->id,
            'nis' => '2026002',
            'name' => 'Siti Aminah',
            'gender' => 'P',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_aggregates_rapor_data_correctly()
    {
        // 1. Create a score record
        StudentSemesterScore::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'semester' => $this->semester->nama,
            'score' => 85.00,
            'predicate' => 'B',
            'description' => 'Menguasai konsep dinamika partikel dengan baik.',
        ]);

        // 2. Create attendance records
        // 3 present, 1 late, 1 sick, 1 alpa
        $teacher = User::factory()->create(['tenant_id' => $this->tenant->id]);

        Attendance::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $teacher->id,
            'attendable_type' => Siswa::class,
            'attendable_id' => $this->siswa->id,
            'date' => '2026-08-01',
            'time' => '07:00:00',
            'type' => 'in',
            'status' => 'present',
        ]);

        Attendance::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $teacher->id,
            'attendable_type' => Siswa::class,
            'attendable_id' => $this->siswa->id,
            'date' => '2026-08-02',
            'time' => '07:05:00',
            'type' => 'in',
            'status' => 'present',
        ]);

        Attendance::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $teacher->id,
            'attendable_type' => Siswa::class,
            'attendable_id' => $this->siswa->id,
            'date' => '2026-08-03',
            'time' => '07:20:00',
            'type' => 'in',
            'status' => 'late',
        ]);

        Permit::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $teacher->id,
            'permitable_type' => Siswa::class,
            'permitable_id' => $this->siswa->id,
            'date' => '2026-08-04',
            'type' => 'sick',
            'reason' => 'Sakit demam',
            'status' => 'approved',
            'approved_by' => $teacher->id,
            'approved_at' => now(),
        ]);

        Absence::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $teacher->id,
            'absentable_type' => Siswa::class,
            'absentable_id' => $this->siswa->id,
            'date' => '2026-08-05',
            'type' => 'alpa',
            'reason' => 'Tanpa keterangan',
        ]);

        // 3. Create Report Note
        ReportNote::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student->id,
            'semester' => $this->semester->nama,
            'note' => 'Pertahankan prestasi belajar dan keaktifan di kelas.',
        ]);

        $service = app(\App\Modules\Evaluation\Services\RaporGeneratorService::class);
        $data = $service->getReportData($this->student, $this->academicYear, $this->semester);

        $this->assertEquals('Siti Aminah', $data['student']->name);
        $this->assertEquals('Kelas X-B', $data['classroom']->name);
        $this->assertCount(1, $data['scores']);
        $this->assertEquals(85.00, $data['scores'][0]->score);

        // Attendance asserts
        $this->assertEquals(2, $data['attendance']['present']);
        $this->assertEquals(1, $data['attendance']['late']);
        $this->assertEquals(1, $data['attendance']['sick']);
        $this->assertEquals(0, $data['attendance']['permission']); // Not seeded
        $this->assertEquals(1, $data['attendance']['absent']); // 1 Absence (Alpa)

        // Note assert
        $this->assertEquals('Pertahankan prestasi belajar dan keaktifan di kelas.', $data['note']);
    }

    /** @test */
    public function it_generates_pdf_report_successfully()
    {
        StudentSemesterScore::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'semester' => $this->semester->nama,
            'score' => 90.00,
            'predicate' => 'A',
            'description' => 'Sangat menguasai konsep fisika dasar.',
        ]);

        $service = app(\App\Modules\Evaluation\Services\RaporGeneratorService::class);
        $pdfContent = $service->generatePdf($this->student, $this->academicYear, $this->semester);

        $this->assertNotEmpty($pdfContent);
        // Standard PDF header signature check
        $this->assertStringStartsWith('%PDF-', $pdfContent);
    }
}
