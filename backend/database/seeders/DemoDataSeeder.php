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

        $teacherPersonId = DB::table('people')->insertGetId([
            'first_name' => 'Juan',
            'last_name' => 'Perez',
            'phone' => '6000-0001',
            'email_personal' => 'profesor@correo.com',
            'email_institucional' => 'profesor@utp.edu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentPersonId = DB::table('people')->insertGetId([
            'first_name' => 'Maria',
            'last_name' => 'Lopez',
            'phone' => '6000-1001',
            'email_personal' => 'maria@gmail.com',
            'email_institucional' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentPersonId2 = DB::table('people')->insertGetId([
            'first_name' => 'Carlos',
            'last_name' => 'Diaz',
            'phone' => '6000-1002',
            'email_personal' => 'carlos@gmail.com',
            'email_institucional' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $summerPersonId = DB::table('people')->insertGetId([
            'first_name' => 'Luis',
            'last_name' => 'Perez',
            'phone' => '6000-2001',
            'email_personal' => 'luis@gmail.com',
            'email_institucional' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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

        $teacherId = DB::table('teachers')->insertGetId([
            'person_id' => $teacherPersonId,
            'language_id' => 'ING-1',
            'status' => 'Activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('students')->insert([
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
        ]);

        DB::table('student_profiles')->insert([
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
        ]);

        DB::table('guardians')->insert([
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
        ]);

        DB::table('student_contacts')->insert([
            [
                'student_id' => 'SV-001',
                'allergies' => 'No',
                'blood_type' => 'O+',
                'emergency_name' => 'Ana Perez',
                'emergency_phone' => '6000-3003',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('groups')->insert([
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
        ]);

        $regularSessionId = DB::table('group_sessions')->insertGetId([
            'group_id' => 'G-001',
            'teacher_id' => $teacherId,
            'start_date' => Carbon::now()->subDays(45)->toDateString(),
            'end_date' => Carbon::now()->subDays(2)->toDateString(),
            'classroom' => 'A-1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $summerSessionId = DB::table('group_sessions')->insertGetId([
            'group_id' => 'GV-001',
            'teacher_id' => $teacherId,
            'start_date' => Carbon::now()->subDays(30)->toDateString(),
            'end_date' => Carbon::now()->subDays(2)->toDateString(),
            'classroom' => 'V-1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('enrollments')->insert([
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
        ]);

        $paymentId = DB::table('payments')->insertGetId([
            'student_id' => 'ST-001',
            'payment_type' => 'Abono',
            'method' => 'Banca en Linea',
            'bank' => 'Banco Demo',
            'account_owner' => 'Maria Lopez',
            'receipt_path' => 'uploads/abonos/ab_demo.jpg',
            'amount' => 50.00,
            'paid_at' => now(),
            'status' => 'Pendiente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('payments')->insert([
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
        ]);

        DB::table('balance_movements')->insert([
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
        ]);

        DB::table('notifications')->insert([
            'student_id' => 'ST-001',
            'title' => 'Bienvenido',
            'body' => 'Tu cuenta fue creada correctamente.',
            'type' => 'estado',
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('promotions')->insert([
            'student_id' => 'ST-001',
            'group_session_id' => $regularSessionId,
            'old_level' => '2',
            'new_level' => '3',
            'approved_by' => 'admin@gmail.com',
            'reverted_at' => null,
            'reverted_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
