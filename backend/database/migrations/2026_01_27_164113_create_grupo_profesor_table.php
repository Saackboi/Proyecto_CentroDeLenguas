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
        Schema::create('grupo_profesor', function (Blueprint $table) {
            $table->string('id_grupo', 10);
            $table->string('id_profesor', 30);
            $table->date('fecha_inicio');
            $table->date('fecha_cierre');

            $table->primary(['id_grupo', 'id_profesor', 'fecha_inicio']);
            $table->foreign('id_grupo')->references('id_grupo')->on('grupos');
            $table->foreign('id_profesor')->references('id_profesor')->on('profesores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_profesor');
    }
};
