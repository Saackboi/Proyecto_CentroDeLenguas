<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CursosIdiomasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('cursos_idiomas')->insert([
            'id_idioma' => 'ING-1',
            'nombre' => 'Ingles',
        ]);
    }
}
