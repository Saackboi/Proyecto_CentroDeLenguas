<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id('id_pago');
            $table->string('id_estudiante', 30);
            $table->enum('tipo_pago', ['PruebaUbicacion', 'Abono']);
            $table->enum('metodo_pago', ['Caja', 'Banca en Linea']);
            $table->string('banco', 50);
            $table->string('propietario_cuenta', 100);
            $table->string('comprobante_imagen', 255);
            $table->decimal('monto', 8, 2);
            $table->dateTime('fecha_pago');
            $table->enum('estado', ['Pendiente', 'Aceptado', 'Rechazado'])->default('Pendiente');

            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiantes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
