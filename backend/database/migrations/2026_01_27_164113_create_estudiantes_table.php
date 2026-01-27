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
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->string('id_estudiante', 30)->primary();
            $table->string('tipo_id', 15);
            $table->string('nombre', 50);
            $table->string('apellido', 50);
            $table->string('correo_personal', 100);
            $table->string('correo_utp', 100)->nullable();
            $table->string('telefono', 20);
            $table->string('nivel', 10)->nullable();
            $table->enum('estado', ['En proceso', 'Activo', 'Inactivo', 'En prueba'])->default('En proceso');
            $table->enum('es_estudiante', ['SI', 'NO'])->default('NO');
            $table->decimal('deuda_total', 8, 2)->nullable();
            $table->decimal('saldo_pendiente', 8, 2)->nullable();
            $table->dateTime('fecha_registro')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
