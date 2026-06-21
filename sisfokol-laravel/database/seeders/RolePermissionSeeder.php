<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Master
            'master.school-profile.view',
            'master.school-profile.update',
            'master.academic-year.*',
            'master.classroom.*',
            'master.room.*',
            'master.subject.*',
            'master.subject-type.*',
            'master.extracurricular.*',
            'master.violation-type.*',
            'master.violation-point.*',
            'master.achievement-type.*',
            'master.counseling-type.*',

            // User Management
            'user.*',
            'user.view',
            'employee.*',
            'employee.view',
            'student.*',
            'student.view',

            // Academic
            'academic.schedule.*',
            'academic.schedule.view',
            'academic.teacher-agenda.*',
            'academic.curriculum.*',

            // Presence & Absence
            'presence.*',
            'presence.view',
            'absence.*',
            'absence.view',
            'permit.*',

            // Discipline
            'violation.*',
            'violation.view',
            'counseling.*',
            'achievement.*',

            // Finance
            'finance.*',
            'finance.payment-item.*',
            'finance.student-bill.*',
            'finance.student-bill.view',
            'finance.student-payment.*',
            'finance.student-payment.view',
            'finance.student-saving.*',
            'finance.report.*',


            // Inventory
            'inventory.*',

            // Reports
            'report.*',

            // Settings
            'setting.*',
            'plugin.activate',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = [
            'admin' => ['*'],
            'principal' => [
                'master.school-profile.view',
                'report.*',
                'user.view',
                'employee.view',
                'student.view',
            ],
            'teacher' => [
                'academic.schedule.view',
                'academic.teacher-agenda.*',
                'academic.curriculum.*',
                'presence.view',
                'absence.view',
            ],
            'student' => [
                'academic.schedule.view',
                'presence.view',
                'absence.view',
                'finance.student-bill.view',
                'finance.student-payment.view',
            ],
            'homeroom-teacher' => [
                'academic.schedule.view',
                'academic.curriculum.*',
                'student.view',
                'absence.*',
                'violation.*',
                'achievement.*',
                'report.*',
            ],
            'finance' => [
                'finance.*',
                'student.view',
                'report.*',
            ],
            'counselor' => [
                'violation.*',
                'counseling.*',
                'achievement.*',
                'student.view',
                'absence.*',
                'permit.*',
            ],
            'picket-officer' => [
                'presence.*',
                'absence.*',
                'permit.*',
                'violation.view',
            ],
            'inventory' => [
                'inventory.*',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);

            if (in_array('*', $rolePermissions)) {
                $role->givePermissionTo(Permission::all());
            } else {
                $role->givePermissionTo($rolePermissions);
            }
        }
    }
}
