<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('languages')->updateOrInsert(
            ['id' => 'ING-1'],
            ['name' => 'Ingles']
        );

        DB::table('people')->updateOrInsert(
            ['email_personal' => 'profesor@correo.com'],
            [
                'first_name' => 'Juan',
                'last_name' => 'Perez',
                'phone' => '6000-0001',
                'email_institucional' => 'profesor@utp.edu',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $teacherPersonId = DB::table('people')
            ->where('email_personal', 'profesor@correo.com')
            ->value('id');

        DB::table('people')->updateOrInsert(
            ['email_personal' => 'maria@gmail.com'],
            [
                'first_name' => 'Maria',
                'last_name' => 'Lopez',
                'phone' => '6000-1001',
                'email_institucional' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $studentPersonId = DB::table('people')
            ->where('email_personal', 'maria@gmail.com')
            ->value('id');

        DB::table('people')->updateOrInsert(
            ['email_personal' => 'carlos@gmail.com'],
            [
                'first_name' => 'Carlos',
                'last_name' => 'Diaz',
                'phone' => '6000-1002',
                'email_institucional' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $studentPersonId2 = DB::table('people')
            ->where('email_personal', 'carlos@gmail.com')
            ->value('id');

        DB::table('people')->updateOrInsert(
            ['email_personal' => 'luis@gmail.com'],
            [
                'first_name' => 'Luis',
                'last_name' => 'Perez',
                'phone' => '6000-2001',
                'email_institucional' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $summerPersonId = DB::table('people')
            ->where('email_personal', 'luis@gmail.com')
            ->value('id');

        DB::table('people')->updateOrInsert(
            ['email_personal' => 'ana.gomez@correo.com'],
            [
                'first_name' => 'Ana',
                'last_name' => 'Gomez',
                'phone' => '6000-0002',
                'email_institucional' => 'agomez@utp.edu',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $teacherPersonId2 = DB::table('people')
            ->where('email_personal', 'ana.gomez@correo.com')
            ->value('id');

        DB::table('people')->updateOrInsert(
            ['email_personal' => 'mario.ruiz@correo.com'],
            [
                'first_name' => 'Mario',
                'last_name' => 'Ruiz',
                'phone' => '6000-0003',
                'email_institucional' => 'mruiz@utp.edu',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $teacherPersonId3 = DB::table('people')
            ->where('email_personal', 'mario.ruiz@correo.com')
            ->value('id');

        DB::table('people')->updateOrInsert(
            ['email_personal' => 'andrea@gmail.com'],
            [
                'first_name' => 'Andrea',
                'last_name' => 'Castro',
                'phone' => '6000-1003',
                'email_institucional' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $studentPersonId3 = DB::table('people')
            ->where('email_personal', 'andrea@gmail.com')
            ->value('id');

        DB::table('people')->updateOrInsert(
            ['email_personal' => 'diego@gmail.com'],
            [
                'first_name' => 'Diego',
                'last_name' => 'Molina',
                'phone' => '6000-1004',
                'email_institucional' => 'diego@utp.edu',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $studentPersonId4 = DB::table('people')
            ->where('email_personal', 'diego@gmail.com')
            ->value('id');

        DB::table('people')->updateOrInsert(
            ['email_personal' => 'rosa@gmail.com'],
            [
                'first_name' => 'Rosa',
                'last_name' => 'Navarro',
                'phone' => '6000-1005',
                'email_institucional' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $studentPersonId5 = DB::table('people')
            ->where('email_personal', 'rosa@gmail.com')
            ->value('id');

        DB::table('people')->updateOrInsert(
            ['email_personal' => 'jorge@gmail.com'],
            [
                'first_name' => 'Jorge',
                'last_name' => 'Salas',
                'phone' => '6000-1006',
                'email_institucional' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $studentPersonId6 = DB::table('people')
            ->where('email_personal', 'jorge@gmail.com')
            ->value('id');

        DB::table('people')->updateOrInsert(
            ['email_personal' => 'karla@gmail.com'],
            [
                'first_name' => 'Karla',
                'last_name' => 'Mendez',
                'phone' => '6000-1007',
                'email_institucional' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $studentPersonId7 = DB::table('people')
            ->where('email_personal', 'karla@gmail.com')
            ->value('id');

        DB::table('people')->updateOrInsert(
            ['email_personal' => 'hector@gmail.com'],
            [
                'first_name' => 'Hector',
                'last_name' => 'Vargas',
                'phone' => '6000-1008',
                'email_institucional' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $studentPersonId8 = DB::table('people')
            ->where('email_personal', 'hector@gmail.com')
            ->value('id');

        DB::table('people')->updateOrInsert(
            ['email_personal' => 'paula@gmail.com'],
            [
                'first_name' => 'Paula',
                'last_name' => 'Vega',
                'phone' => '6000-2002',
                'email_institucional' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $summerPersonId2 = DB::table('people')
            ->where('email_personal', 'paula@gmail.com')
            ->value('id');

        DB::table('people')->updateOrInsert(
            ['email_personal' => 'sofia@gmail.com'],
            [
                'first_name' => 'Sofia',
                'last_name' => 'Torres',
                'phone' => '6000-2003',
                'email_institucional' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $summerPersonId3 = DB::table('people')
            ->where('email_personal', 'sofia@gmail.com')
            ->value('id');

        DB::table('users')->updateOrInsert(
            ['email' => 'profesor@correo.com'],
            [
                'password' => Hash::make('Profesor123'),
                'role' => 'Profesor',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'maria@gmail.com'],
            [
                'password' => Hash::make('Estudiante123'),
                'role' => 'Estudiante',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'ana.gomez@correo.com'],
            [
                'password' => Hash::make('Profesor123'),
                'role' => 'Profesor',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'mario.ruiz@correo.com'],
            [
                'password' => Hash::make('Profesor123'),
                'role' => 'Profesor',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'andrea@gmail.com'],
            [
                'password' => Hash::make('Estudiante123'),
                'role' => 'Estudiante',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'diego@gmail.com'],
            [
                'password' => Hash::make('Estudiante123'),
                'role' => 'Estudiante',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('teachers')->updateOrInsert(
            ['person_id' => $teacherPersonId],
            [
                'language_id' => 'ING-1',
                'status' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $teacherId = DB::table('teachers')
            ->where('person_id', $teacherPersonId)
            ->value('id');

        DB::table('teachers')->updateOrInsert(
            ['person_id' => $teacherPersonId2],
            [
                'language_id' => 'FRA-1',
                'status' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $teacherId2 = DB::table('teachers')
            ->where('person_id', $teacherPersonId2)
            ->value('id');

        DB::table('teachers')->updateOrInsert(
            ['person_id' => $teacherPersonId3],
            [
                'language_id' => 'POR-1',
                'status' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $teacherId3 = DB::table('teachers')
            ->where('person_id', $teacherPersonId3)
            ->value('id');

        DB::table('students')->upsert([
            [
                'id' => 'ST-001',
                'person_id' => $studentPersonId,
                'type' => 'regular',
                'status' => 'Activo',
                'level' => '3',
                'is_utp' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'ST-002',
                'person_id' => $studentPersonId2,
                'type' => 'regular',
                'status' => 'Activo',
                'level' => '3',
                'is_utp' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'SV-001',
                'person_id' => $summerPersonId,
                'type' => 'verano',
                'status' => 'Activo',
                'level' => '2',
                'is_utp' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'ST-003',
                'person_id' => $studentPersonId3,
                'type' => 'regular',
                'status' => 'En proceso',
                'level' => null,
                'is_utp' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'ST-004',
                'person_id' => $studentPersonId4,
                'type' => 'regular',
                'status' => 'En prueba',
                'level' => null,
                'is_utp' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'ST-005',
                'person_id' => $studentPersonId5,
                'type' => 'regular',
                'status' => 'Activo',
                'level' => '4',
                'is_utp' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'ST-006',
                'person_id' => $studentPersonId6,
                'type' => 'regular',
                'status' => 'Activo',
                'level' => '1',
                'is_utp' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'SV-002',
                'person_id' => $summerPersonId2,
                'type' => 'verano',
                'status' => 'En proceso',
                'level' => '1',
                'is_utp' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'SV-003',
                'person_id' => $summerPersonId3,
                'type' => 'verano',
                'status' => 'En proceso',
                'level' => '2',
                'is_utp' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'ST-007',
                'person_id' => $studentPersonId7,
                'type' => 'regular',
                'status' => 'En proceso',
                'level' => null,
                'is_utp' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'ST-008',
                'person_id' => $studentPersonId8,
                'type' => 'regular',
                'status' => 'En prueba',
                'level' => null,
                'is_utp' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['id'], ['person_id', 'type', 'status', 'level', 'is_utp', 'created_at', 'updated_at']);

        DB::table('student_profiles')->upsert([
            [
                'student_id' => 'ST-001',
                'birth_date' => '2003-05-12',
                'home_number' => '12',
                'address' => 'Penonome',
                'gender' => 'Femenino',
                'school' => 'Colegio Central',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'ST-002',
                'birth_date' => '2002-10-22',
                'home_number' => '14',
                'address' => 'Aguadulce',
                'gender' => 'Masculino',
                'school' => 'Colegio Basico',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'SV-001',
                'birth_date' => '2010-05-20',
                'home_number' => '20',
                'address' => 'Penonome',
                'gender' => 'Masculino',
                'school' => 'Colegio X',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'ST-005',
                'birth_date' => '2001-04-10',
                'home_number' => '18',
                'address' => 'Chitre',
                'gender' => 'Femenino',
                'school' => 'Instituto A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'ST-006',
                'birth_date' => '2004-09-30',
                'home_number' => '9',
                'address' => 'Aguadulce',
                'gender' => 'Masculino',
                'school' => 'Instituto B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'SV-002',
                'birth_date' => '2011-03-15',
                'home_number' => '5',
                'address' => 'Penonome',
                'gender' => 'Femenino',
                'school' => 'Colegio Z',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'SV-003',
                'birth_date' => '2012-11-02',
                'home_number' => '22',
                'address' => 'Anton',
                'gender' => 'Femenino',
                'school' => 'Colegio Y',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['student_id'], ['birth_date', 'home_number', 'address', 'gender', 'school', 'created_at', 'updated_at']);

        DB::table('guardians')->upsert([
            [
                'student_id' => 'SV-001',
                'father_name' => 'Pedro Perez',
                'father_workplace' => 'Empresa X',
                'father_work_phone' => '2222-2222',
                'father_phone' => '6000-3001',
                'mother_name' => 'Maria Perez',
                'mother_workplace' => 'Empresa Y',
                'mother_work_phone' => '2222-3333',
                'mother_phone' => '6000-3002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'SV-002',
                'father_name' => 'Luis Vega',
                'father_workplace' => 'Empresa Z',
                'father_work_phone' => '2222-4444',
                'father_phone' => '6000-3004',
                'mother_name' => 'Carla Vega',
                'mother_workplace' => 'Empresa W',
                'mother_work_phone' => '2222-5555',
                'mother_phone' => '6000-3005',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'SV-003',
                'father_name' => 'Ramon Torres',
                'father_workplace' => 'Empresa Q',
                'father_work_phone' => '2222-6666',
                'father_phone' => '6000-3006',
                'mother_name' => 'Julia Torres',
                'mother_workplace' => 'Empresa R',
                'mother_work_phone' => '2222-7777',
                'mother_phone' => '6000-3007',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['student_id'], ['father_name', 'father_workplace', 'father_work_phone', 'father_phone', 'mother_name', 'mother_workplace', 'mother_work_phone', 'mother_phone', 'created_at', 'updated_at']);

        DB::table('student_contacts')->upsert([
            [
                'student_id' => 'SV-001',
                'allergies' => 'No',
                'blood_type' => 'O+',
                'emergency_name' => 'Ana Perez',
                'emergency_phone' => '6000-3003',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'SV-002',
                'allergies' => 'Polen',
                'blood_type' => 'A+',
                'emergency_name' => 'Laura Vega',
                'emergency_phone' => '6000-3008',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'SV-003',
                'allergies' => 'No',
                'blood_type' => 'B+',
                'emergency_name' => 'Alberto Torres',
                'emergency_phone' => '6000-3009',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['student_id'], ['allergies', 'blood_type', 'emergency_name', 'emergency_phone', 'created_at', 'updated_at']);

        DB::table('groups')->upsert([
            [
                'id' => 'G-001',
                'language_id' => 'ING-1',
                'level' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'GV-001',
                'language_id' => 'ING-1',
                'level' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'G-002',
                'language_id' => 'ING-1',
                'level' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'G-003',
                'language_id' => 'FRA-1',
                'level' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'G-004',
                'language_id' => 'POR-1',
                'level' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'GV-002',
                'language_id' => 'ING-1',
                'level' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'GV-003',
                'language_id' => 'FRA-1',
                'level' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['id'], ['language_id', 'level', 'created_at', 'updated_at']);

        $regularStartDate = Carbon::now()->subDays(45)->toDateString();
        $regularEndDate = Carbon::now()->subDays(2)->toDateString();

        DB::table('group_sessions')->updateOrInsert(
            ['group_id' => 'G-001', 'start_date' => $regularStartDate, 'end_date' => $regularEndDate],
            [
                'teacher_id' => $teacherId,
                'classroom' => 'A-1',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $regularSessionId = DB::table('group_sessions')
            ->where('group_id', 'G-001')
            ->where('start_date', $regularStartDate)
            ->where('end_date', $regularEndDate)
            ->value('id');

        $summerStartDate = Carbon::now()->subDays(30)->toDateString();
        $summerEndDate = Carbon::now()->subDays(2)->toDateString();

        DB::table('group_sessions')->updateOrInsert(
            ['group_id' => 'GV-001', 'start_date' => $summerStartDate, 'end_date' => $summerEndDate],
            [
                'teacher_id' => $teacherId,
                'classroom' => 'V-1',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $summerSessionId = DB::table('group_sessions')
            ->where('group_id', 'GV-001')
            ->where('start_date', $summerStartDate)
            ->where('end_date', $summerEndDate)
            ->value('id');

        $regularStartDate2 = Carbon::now()->subDays(20)->toDateString();
        $regularEndDate2 = Carbon::now()->addDays(40)->toDateString();

        DB::table('group_sessions')->updateOrInsert(
            ['group_id' => 'G-002', 'start_date' => $regularStartDate2, 'end_date' => $regularEndDate2],
            [
                'teacher_id' => $teacherId,
                'classroom' => 'A-2',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $regularSessionId2 = DB::table('group_sessions')
            ->where('group_id', 'G-002')
            ->where('start_date', $regularStartDate2)
            ->where('end_date', $regularEndDate2)
            ->value('id');

        $regularStartDate3 = Carbon::now()->subDays(10)->toDateString();
        $regularEndDate3 = Carbon::now()->addDays(50)->toDateString();

        DB::table('group_sessions')->updateOrInsert(
            ['group_id' => 'G-003', 'start_date' => $regularStartDate3, 'end_date' => $regularEndDate3],
            [
                'teacher_id' => $teacherId2,
                'classroom' => 'F-1',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $regularSessionId3 = DB::table('group_sessions')
            ->where('group_id', 'G-003')
            ->where('start_date', $regularStartDate3)
            ->where('end_date', $regularEndDate3)
            ->value('id');

        $regularStartDate4 = Carbon::now()->subDays(5)->toDateString();
        $regularEndDate4 = Carbon::now()->addDays(55)->toDateString();

        DB::table('group_sessions')->updateOrInsert(
            ['group_id' => 'G-004', 'start_date' => $regularStartDate4, 'end_date' => $regularEndDate4],
            [
                'teacher_id' => $teacherId3,
                'classroom' => 'P-1',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $regularSessionId4 = DB::table('group_sessions')
            ->where('group_id', 'G-004')
            ->where('start_date', $regularStartDate4)
            ->where('end_date', $regularEndDate4)
            ->value('id');

        $summerStartDate2 = Carbon::now()->subDays(15)->toDateString();
        $summerEndDate2 = Carbon::now()->addDays(25)->toDateString();

        DB::table('group_sessions')->updateOrInsert(
            ['group_id' => 'GV-002', 'start_date' => $summerStartDate2, 'end_date' => $summerEndDate2],
            [
                'teacher_id' => $teacherId2,
                'classroom' => 'V-2',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $summerSessionId2 = DB::table('group_sessions')
            ->where('group_id', 'GV-002')
            ->where('start_date', $summerStartDate2)
            ->where('end_date', $summerEndDate2)
            ->value('id');

        $summerStartDate3 = Carbon::now()->subDays(12)->toDateString();
        $summerEndDate3 = Carbon::now()->addDays(28)->toDateString();

        DB::table('group_sessions')->updateOrInsert(
            ['group_id' => 'GV-003', 'start_date' => $summerStartDate3, 'end_date' => $summerEndDate3],
            [
                'teacher_id' => $teacherId3,
                'classroom' => 'V-3',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $summerSessionId3 = DB::table('group_sessions')
            ->where('group_id', 'GV-003')
            ->where('start_date', $summerStartDate3)
            ->where('end_date', $summerEndDate3)
            ->value('id');

        DB::table('enrollments')->upsert([
            [
                'student_id' => 'ST-001',
                'group_session_id' => $regularSessionId,
                'final_grade' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'ST-002',
                'group_session_id' => $regularSessionId,
                'final_grade' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'SV-001',
                'group_session_id' => $summerSessionId,
                'final_grade' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'ST-005',
                'group_session_id' => $regularSessionId2,
                'final_grade' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'ST-006',
                'group_session_id' => $regularSessionId3,
                'final_grade' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'SV-002',
                'group_session_id' => $summerSessionId2,
                'final_grade' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'SV-003',
                'group_session_id' => $summerSessionId3,
                'final_grade' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['student_id', 'group_session_id'], ['final_grade', 'created_at', 'updated_at']);

        DB::table('payments')->updateOrInsert(
            ['receipt_path' => 'uploads/abonos/ab_demo.jpg'],
            [
                'student_id' => 'ST-001',
                'payment_type' => 'Abono',
                'method' => 'Banca en Linea',
                'bank' => 'Banco Demo',
                'account_owner' => 'Maria Lopez',
                'amount' => 50.00,
                'paid_at' => now(),
                'status' => 'Pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $paymentId = DB::table('payments')
            ->where('receipt_path', 'uploads/abonos/ab_demo.jpg')
            ->value('id');

        DB::table('payments')->updateOrInsert(
            ['receipt_path' => 'uploads/abonos/ab_demo2.jpg'],
            [
                'student_id' => 'ST-005',
                'payment_type' => 'Abono',
                'method' => 'Caja',
                'bank' => null,
                'account_owner' => null,
                'amount' => 30.00,
                'paid_at' => now(),
                'status' => 'Pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $paymentId2 = DB::table('payments')
            ->where('receipt_path', 'uploads/abonos/ab_demo2.jpg')
            ->value('id');

        DB::table('payments')->updateOrInsert(
            ['receipt_path' => 'uploads/abonos/ab_demo3.jpg'],
            [
                'student_id' => 'ST-006',
                'payment_type' => 'Abono',
                'method' => 'Banca en Linea',
                'bank' => 'Banco Demo',
                'account_owner' => 'Jorge Salas',
                'amount' => 90.00,
                'paid_at' => now()->subDays(1),
                'status' => 'Aceptado',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $paymentId3 = DB::table('payments')
            ->where('receipt_path', 'uploads/abonos/ab_demo3.jpg')
            ->value('id');

        DB::table('payments')->upsert([
            'student_id' => 'ST-002',
            'payment_type' => 'PruebaUbicacion',
            'method' => 'Caja',
            'bank' => null,
            'account_owner' => null,
            'receipt_path' => 'uploads/ubicacion/pb_demo.jpg',
            'amount' => 10.00,
            'paid_at' => now(),
            'status' => 'Pendiente',
            'created_at' => now(),
            'updated_at' => now(),
        ], ['receipt_path'], ['student_id', 'payment_type', 'method', 'bank', 'account_owner', 'amount', 'paid_at', 'status', 'created_at', 'updated_at']);

        DB::table('payments')->upsert([
            [
                'student_id' => 'ST-003',
                'payment_type' => 'PruebaUbicacion',
                'method' => 'Banca en Linea',
                'bank' => 'Banco Demo',
                'account_owner' => 'Andrea Castro',
                'receipt_path' => 'uploads/ubicacion/pb_demo2.jpg',
                'amount' => 10.00,
                'paid_at' => now()->subDays(2),
                'status' => 'Pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'ST-004',
                'payment_type' => 'PruebaUbicacion',
                'method' => 'Caja',
                'bank' => null,
                'account_owner' => null,
                'receipt_path' => 'uploads/ubicacion/pb_demo3.jpg',
                'amount' => 10.00,
                'paid_at' => now()->subDays(1),
                'status' => 'Pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'ST-007',
                'payment_type' => 'PruebaUbicacion',
                'method' => 'Caja',
                'bank' => null,
                'account_owner' => null,
                'receipt_path' => 'uploads/ubicacion/pb_demo4.jpg',
                'amount' => 10.00,
                'paid_at' => now()->subDays(3),
                'status' => 'Pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'ST-008',
                'payment_type' => 'PruebaUbicacion',
                'method' => 'Banca en Linea',
                'bank' => 'Banco Demo',
                'account_owner' => 'Hector Vargas',
                'receipt_path' => 'uploads/ubicacion/pb_demo5.jpg',
                'amount' => 10.00,
                'paid_at' => now()->subDays(4),
                'status' => 'Pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['receipt_path'], ['student_id', 'payment_type', 'method', 'bank', 'account_owner', 'amount', 'paid_at', 'status', 'created_at', 'updated_at']);

        DB::table('balance_movements')->upsert([
            [
                'student_id' => 'ST-001',
                'movement_type' => 'charge',
                'amount' => 90.00,
                'reason' => 'matricula',
                'payment_id' => null,
                'group_session_id' => $regularSessionId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'ST-001',
                'movement_type' => 'payment',
                'amount' => 50.00,
                'reason' => 'abono',
                'payment_id' => $paymentId,
                'group_session_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'ST-002',
                'movement_type' => 'charge',
                'amount' => 90.00,
                'reason' => 'matricula',
                'payment_id' => null,
                'group_session_id' => $regularSessionId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'ST-005',
                'movement_type' => 'charge',
                'amount' => 90.00,
                'reason' => 'matricula',
                'payment_id' => null,
                'group_session_id' => $regularSessionId2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'ST-006',
                'movement_type' => 'charge',
                'amount' => 90.00,
                'reason' => 'matricula',
                'payment_id' => null,
                'group_session_id' => $regularSessionId3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'ST-006',
                'movement_type' => 'payment',
                'amount' => 90.00,
                'reason' => 'abono',
                'payment_id' => $paymentId3,
                'group_session_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['student_id', 'movement_type', 'amount', 'reason', 'payment_id', 'group_session_id'], ['created_at', 'updated_at']);

        DB::table('notifications')->updateOrInsert(
            ['student_id' => 'ST-001', 'title' => 'Bienvenido'],
            [
                'body' => 'Tu cuenta fue creada correctamente.',
                'type' => 'estado',
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('notifications')->upsert([
            [
                'student_id' => 'ST-002',
                'title' => 'Abono pendiente',
                'body' => 'Tu abono fue recibido y esta en revision.',
                'type' => 'abono',
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'ST-005',
                'title' => 'Inscripcion activa',
                'body' => 'Tu inscripcion fue activada correctamente.',
                'type' => 'estado',
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'SV-002',
                'title' => 'Solicitud registrada',
                'body' => 'Tu solicitud de verano fue registrada.',
                'type' => 'verano',
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['student_id', 'title'], ['body', 'type', 'read_at', 'created_at', 'updated_at']);

        DB::table('promotions')->updateOrInsert(
            [
                'student_id' => 'ST-001',
                'group_session_id' => $regularSessionId,
                'old_level' => '2',
                'new_level' => '3',
            ],
            [
                'approved_by' => 'admin@gmail.com',
                'reverted_at' => null,
                'reverted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('promotions')->upsert([
            [
                'student_id' => 'ST-002',
                'group_session_id' => $regularSessionId,
                'old_level' => '2',
                'new_level' => '3',
                'approved_by' => 'admin@gmail.com',
                'reverted_at' => null,
                'reverted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => 'ST-005',
                'group_session_id' => $regularSessionId2,
                'old_level' => '3',
                'new_level' => '4',
                'approved_by' => 'admin@gmail.com',
                'reverted_at' => null,
                'reverted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['student_id', 'group_session_id', 'old_level', 'new_level'], ['approved_by', 'reverted_at', 'reverted_by', 'created_at', 'updated_at']);
    }
}
