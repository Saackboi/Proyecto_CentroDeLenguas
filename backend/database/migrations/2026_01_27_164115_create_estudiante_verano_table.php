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
        Schema::create('estudiante_verano', function (Blueprint $table) {
            $table->string('id_estudiante', 30)->primary();
            $table->string('id_familiar', 30);
            $table->string('nivel', 10)->nullable();
            $table->string('nombre_completo', 100);
            $table->string('celular', 20);
            $table->date('fecha_nacimiento');
            $table->string('numero_casa', 10)->nullable();
            $table->string('domicilio', 100);
            $table->string('firma_familiar_imagen', 255)->nullable();
            $table->string('cedula_familiar_imagen', 255)->nullable();
            $table->string('cedula_estudiante_imagen', 255)->nullable();
            $table->enum('sexo', ['Masculino', 'Femenino']);
            $table->enum('estado', ['En proceso', 'Activo', 'Inactivo'])->default('En proceso');
            $table->string('correo', 100);
            $table->string('colegio', 100);
            $table->dateTime('fecha_registro')->useCurrent();

            $table->foreign('id_familiar')->references('id_familiar')->on('familiar_verano');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiante_verano');
    }
};
