<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SaldoService
{
    public function obtenerMontoMatricula(string $idEstudiante): float
    {
        $isUtp = DB::table('students')
            ->where('id', $idEstudiante)
            ->value('is_utp');

        return $isUtp ? 90.00 : 100.00;
    }

    public function crearMovimientoSaldo(
        string $idEstudiante,
        string $tipo,
        float $monto,
        string $motivo,
        ?string $idGrupo = null,
        ?int $idPago = null
    ): void {
        $movementType = match ($tipo) {
            'cargo' => 'charge',
            'abono' => 'payment',
            'ajuste' => 'adjustment',
            default => $tipo,
        };

        DB::table('balance_movements')->insert([
            'student_id' => $idEstudiante,
            'movement_type' => $movementType,
            'amount' => $monto,
            'reason' => $motivo,
            'group_session_id' => $idGrupo,
            'payment_id' => $idPago,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function calcularSaldo(string $idEstudiante): float
    {
        $cargos = (float) DB::table('balance_movements')
            ->where('student_id', $idEstudiante)
            ->whereIn('movement_type', ['charge', 'adjustment'])
            ->sum('amount');

        $abonos = (float) DB::table('balance_movements')
            ->where('student_id', $idEstudiante)
            ->where('movement_type', 'payment')
            ->sum('amount');

        return max(0.00, $cargos - $abonos);
    }

    public function actualizarSaldoCache(string $idEstudiante): void
    {
        $this->calcularSaldo($idEstudiante);
    }
}
