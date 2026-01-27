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
        Schema::create('familiar_verano', function (Blueprint $table) {
            $table->string('id_familiar', 30)->primary();
            $table->string('nombre_padre', 100)->nullable();
            $table->string('lugar_trabajo_padre', 100)->nullable();
            $table->string('telefono_trabajo_padre', 20)->nullable();
            $table->string('celular_padre', 20)->nullable();
            $table->string('nombre_madre', 100)->nullable();
            $table->string('lugar_trabajo_madre', 100)->nullable();
            $table->string('telefono_trabajo_madre', 20)->nullable();
            $table->string('celular_madre', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('familiar_verano');
    }
};
