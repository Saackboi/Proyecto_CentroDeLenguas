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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->string('correo', 100)->primary();
            $table->string('contrasena', 255);
            $table->enum('tipo_usuario', ['Admin', 'Profesor', 'Estudiante'])->default('Profesor');
            $table->string('token_recuperacion', 255)->nullable();
            $table->dateTime('expiracion_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
