<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\RoleHelper;

use App\Models\User;
use App\Models\Role;
use App\Course\Models\Course;
use App\Course\Models\CourseMember;
use App\Counselling\Models\CounsellingSetup;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // add editing teacher
        $editingTeacher = User::create([
            'name' => 'Kurstrainer',
            'email' => 'teacher-vikl@th-nuernberg.de',
            'password' => bcrypt(env('APP_ADMIN_PASSWORD')),
            'main_role_id' => RoleHelper::getIdFromTitle('editingteacher'),
        ]);

        // add student
        $student = User::create([
            'name' => 'Student',
            'email' => 'student-vikl@th-nuernberg.de',
            'password' => bcrypt(env('APP_ADMIN_PASSWORD')),
        ]);

        // add course
        $course = Course::create([
            'name' => 'Querschnitt Onlineberatung WS23/24',
            'enrollment_key' => '123456',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(30),
            'creator_id' => $editingTeacher->id,
        ]);

        $counsellingSetups = [
            [
                "id" => 1,
                "created_at" => "2023-12-09T17:39:00.000000Z",
                "updated_at" => "2023-12-09T17:39:00.000000Z",
                "mandatory" => false,
                "due_date" => null,
                "settings" => [
                    "counselling_fields" => [2, 3],
                    "personae" => [4, 1]
                ],
                "course_id" => 1
            ],
            [
                "id" => 2,
                "created_at" => "2023-12-09T17:39:00.000000Z",
                "updated_at" => "2023-12-09T17:39:00.000000Z",
                "mandatory" => true,
                "due_date" => "2023-12-25 00:00:00",
                "settings" => [
                    "counselling_fields" => [2],
                    "personae" => [4]
                ],
                "course_id" => 1
            ]
        ];
        
        foreach ($counsellingSetups as $setup_data) {
            $mandatory = $setup_data['mandatory'] ?? false;
            $dueDate = $setup_data['due_date'] ?? null;
            $settings = $setup_data['settings'];

            $counsellingSetup = CounsellingSetup::create([
                'mandatory' => $mandatory,
                'due_date' => $dueDate,
                'course_id' => $course->id,
                'settings' => $settings
            ]);
        }

        // add teacher to course
        CourseMember::create([
            'course_id' => $course->id,
            'user_id' => $editingTeacher->id,
            'role_id' => RoleHelper::getIdFromTitle('editingteacher'),
        ]);

        // add student to course
        CourseMember::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'role_id' => RoleHelper::getIdFromTitle('student'),
            'pseudo_first_name' => 'Sofia',
            'pseudo_last_name' => 'Meier'
        ]);
    }
}
