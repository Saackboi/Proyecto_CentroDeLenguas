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
        Schema::create('profesores', function (Blueprint $table) {
            $table->string('id_profesor', 30)->primary();
            $table->string('nombre', 50);
            $table->string('apellido', 50);
            $table->string('id_idioma', 15);
            $table->string('correo', 100);
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');

            $table->foreign('correo')->references('correo')->on('usuarios')->onUpdate('cascade');
            $table->foreign('id_idioma')->references('id_idioma')->on('cursos_idiomas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profesores');
    }
};
