<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SchoolProfileSeeder::class,
            AcademicYearSeeder::class,
            DaySeeder::class,
            HourSeeder::class,
            TimeSlotSeeder::class,
            SubjectTypeSeeder::class,
            AttendanceTimeSeeder::class,
            UserSeeder::class,
            DemoSeeder::class,
            ClassroomSeeder::class,
            MenuSeeder::class,
            FieldSeeder::class,
        ]);
    }
}
