<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('usuarios')->updateOrInsert(
            ['correo' => 'admin@gmail.com'],
            [
                'contrasena' => Hash::make('Admin12345'),
                'tipo_usuario' => 'Admin',
            ]
        );
    }
}
