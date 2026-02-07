<?php

namespace App\Services;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LandingAnnouncementService
{
    private const KEY = 'landing_card';

    private const STATUS_MAP = [
        'abiertas' => [
            'title' => 'Matriculas Abiertas',
            'subtitle' => 'Inscripciones disponibles para el proximo ciclo.',
        ],
        'cerradas' => [
            'title' => 'Matriculas Cerradas',
            'subtitle' => 'Las inscripciones estan cerradas por ahora.',
        ],
        'proximamente' => [
            'title' => 'Proximo Inicio',
            'subtitle' => 'Matriculas disponibles proximamente.',
        ],
        'aviso' => [
            'title' => 'Aviso Importante',
            'subtitle' => 'Consulta novedades antes de inscribirte.',
        ],
    ];

    public function getPublicAnnouncement()
    {
        $registro = DB::table('landing_announcements')
            ->where('key', self::KEY)
            ->where('is_active', true)
            ->first();

        if (!$registro) {
            $fallback = $this->buildAnnouncement('abiertas');
            $fallback['updated_at'] = null;
            return ApiResponse::success($fallback);
        }

        return ApiResponse::success([
            'status_code' => $registro->status_code,
            'title' => $registro->title,
            'subtitle' => $registro->subtitle,
            'updated_at' => $registro->updated_at,
        ]);
    }

    public function updateStatus(Request $request)
    {
        if ($response = $this->asegurarAdmin()) {
            return $response;
        }

        $validated = $request->validate([
            'status_code' => ['required', 'string', 'in:abiertas,cerradas,proximamente,aviso'],
        ]);

        $payload = $this->buildAnnouncement($validated['status_code']);

        DB::table('landing_announcements')->updateOrInsert(
            ['key' => self::KEY],
            [
                'status_code' => $payload['status_code'],
                'title' => $payload['title'],
                'subtitle' => $payload['subtitle'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return ApiResponse::success([
            ...$payload,
            'updated_at' => now(),
        ], 'OK');
    }

    private function asegurarAdmin()
    {
        $usuario = auth('api')->user();
        if (!$usuario || $usuario->role !== 'Admin') {
            return ApiResponse::forbidden('No autorizado.');
        }

        return null;
    }

    private function buildAnnouncement(string $statusCode): array
    {
        $data = self::STATUS_MAP[$statusCode] ?? self::STATUS_MAP['aviso'];

        return [
            'status_code' => $statusCode,
            'title' => $data['title'],
            'subtitle' => $data['subtitle'],
        ];
    }
}
