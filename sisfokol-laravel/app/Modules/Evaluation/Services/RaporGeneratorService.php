<?php

namespace App\Modules\Evaluation\Services;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Absence;
use App\Models\Permit;
use App\Models\ReportNote;
use App\Models\Student;
use App\Models\StudentSemesterScore;
use App\Models\Classroom;
use App\Modules\Academic\Models\Semester;
use App\Modules\Academic\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;

class RaporGeneratorService
{
    /**
     * Compile all data required for the report card.
     */
    public function getReportData(Student $student, AcademicYear $academicYear, Semester $semester): array
    {
        // 1. Fetch Classroom
        $classroom = Classroom::find($student->classroom_id);

        // 2. Fetch Semester Scores
        $scores = StudentSemesterScore::with('subject')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('semester', $semester->nama)
            ->get();

        // 3. Fetch Attendance Stats
        // present days
        $present = Attendance::where('attendable_type', Siswa::class)
            ->where('attendable_id', $student->id)
            ->where('status', 'present')
            ->whereBetween('date', [$semester->tanggal_mulai, $semester->tanggal_selesai])
            ->count();

        // late days
        $late = Attendance::where('attendable_type', Siswa::class)
            ->where('attendable_id', $student->id)
            ->where('status', 'late')
            ->whereBetween('date', [$semester->tanggal_mulai, $semester->tanggal_selesai])
            ->count();

        // sick days (approved permits)
        $sick = Permit::where('permitable_type', Siswa::class)
            ->where('permitable_id', $student->id)
            ->where('type', 'sick')
            ->where('status', 'approved')
            ->whereBetween('date', [$semester->tanggal_mulai, $semester->tanggal_selesai])
            ->count();

        // permission days (approved permits)
        $permission = Permit::where('permitable_type', Siswa::class)
            ->where('permitable_id', $student->id)
            ->where('type', 'permission')
            ->where('status', 'approved')
            ->whereBetween('date', [$semester->tanggal_mulai, $semester->tanggal_selesai])
            ->count();

        // alpa days
        $absent = Absence::where('absentable_type', Siswa::class)
            ->where('absentable_id', $student->id)
            ->whereBetween('date', [$semester->tanggal_mulai, $semester->tanggal_selesai])
            ->count();

        // 4. Fetch Report Note
        $reportNoteRecord = ReportNote::where('student_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('semester', $semester->nama)
            ->first();

        $note = $reportNoteRecord ? $reportNoteRecord->note : 'Tingkatkan terus motivasi belajarmu untuk meraih cita-cita.';

        return [
            'student' => $student,
            'classroom' => $classroom,
            'academicYear' => $academicYear,
            'semester' => $semester,
            'scores' => $scores,
            'attendance' => [
                'present' => $present,
                'late' => $late,
                'sick' => $sick,
                'permission' => $permission,
                'absent' => $absent,
            ],
            'note' => $note,
        ];
    }

    /**
     * Generate and output PDF raw content.
     */
    public function generatePdf(Student $student, AcademicYear $academicYear, Semester $semester): string
    {
        $data = $this->getReportData($student, $academicYear, $semester);
        
        $pdf = Pdf::loadView('evaluation.rapor.pdf', $data);
        
        // DomPDF configuration
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->output();
    }
}
