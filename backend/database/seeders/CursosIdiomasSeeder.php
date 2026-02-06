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
        DB::table('languages')->updateOrInsert(
            ['id' => 'ING-1'],
            ['name' => 'Ingles']
        );

        DB::table('languages')->updateOrInsert(
            ['id' => 'FRA-1'],
            ['name' => 'Frances']
        );

        DB::table('languages')->updateOrInsert(
            ['id' => 'POR-1'],
            ['name' => 'Portugues']
        );
    }
}
