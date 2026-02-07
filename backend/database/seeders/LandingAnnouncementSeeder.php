<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LandingAnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('landing_announcements')->updateOrInsert(
            ['key' => 'landing_card'],
            [
                'status_code' => 'abiertas',
                'title' => 'Matriculas Abiertas',
                'subtitle' => 'Inscripciones disponibles para el proximo ciclo.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
