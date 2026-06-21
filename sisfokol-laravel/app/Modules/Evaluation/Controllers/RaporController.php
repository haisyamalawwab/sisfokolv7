<?php

namespace App\Modules\Evaluation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Modules\Academic\Models\Semester;
use App\Modules\Evaluation\Services\RaporGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RaporController extends Controller
{
    protected RaporGeneratorService $raporService;

    public function __construct(RaporGeneratorService $raporService)
    {
        $this->raporService = $raporService;
    }

    /**
     * Display a list of students for report cards.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $classrooms = collect();
        $selectedClassroomId = $request->input('classroom_id');

        // If SuperAdmin or Admin
        if ($user->isSuperAdmin() || $user->hasRole(['admin_sekolah', 'admin']) || $user->tipe === 'admin_sekolah') {
            $classrooms = Classroom::all();
            $classroom = $selectedClassroomId ? Classroom::find($selectedClassroomId) : $classrooms->first();
        } else {
            // It's a Wali Kelas (homeroom-teacher)
            $employeeId = $user->userable_id;
            $classroom = Classroom::where('homeroom_teacher_id', $employeeId)->first();
        }

        $students = collect();
        if ($classroom) {
            $students = Student::where('classroom_id', $classroom->id)->get();
        }

        return view('evaluation.rapor.index', compact('classrooms', 'classroom', 'students'));
    }

    /**
     * Preview report card in HTML.
     */
    public function show(Student $student)
    {
        // Check if student belongs to user's tenant
        if (!Auth::user()->isSuperAdmin() && Auth::user()->tenant_id !== $student->tenant_id) {
            abort(403, 'Unauthorized.');
        }

        $academicYear = AcademicYear::active();
        $semester = Semester::where('aktif', true)->first();

        if (!$academicYear || !$semester) {
            return redirect()->back()->with('error', 'Tahun ajaran atau semester aktif tidak ditemukan.');
        }

        $data = $this->raporService->getReportData($student, $academicYear, $semester);

        return view('evaluation.rapor.show', $data);
    }

    /**
     * Download or view report card PDF.
     */
    public function downloadPdf(Student $student)
    {
        if (!Auth::user()->isSuperAdmin() && Auth::user()->tenant_id !== $student->tenant_id) {
            abort(403, 'Unauthorized.');
        }

        $academicYear = AcademicYear::active();
        $semester = Semester::where('aktif', true)->first();

        if (!$academicYear || !$semester) {
            return redirect()->back()->with('error', 'Tahun ajaran atau semester aktif tidak ditemukan.');
        }

        $pdfContent = $this->raporService->generatePdf($student, $academicYear, $semester);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Rapor_' . $student->nis . '_' . str_replace(' ', '_', $student->name) . '.pdf"');
    }
}
