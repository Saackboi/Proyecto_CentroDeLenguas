<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('promotions');
        Schema::dropIfExists('balance_movements');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('group_sessions');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('students');
        Schema::dropIfExists('student_contacts');
        Schema::dropIfExists('guardians');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('people');
        Schema::dropIfExists('users');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('notifications');

        Schema::dropIfExists('promociones');
        Schema::dropIfExists('movimientos_saldo');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('grupos_estudiante_verano');
        Schema::dropIfExists('grupos_estudiantes');
        Schema::dropIfExists('grupo_profesor');
        Schema::dropIfExists('grupos');
        Schema::dropIfExists('profesores');
        Schema::dropIfExists('estudiante_verano');
        Schema::dropIfExists('estudiantes');
        Schema::dropIfExists('familiar_verano');
        Schema::dropIfExists('estudiante_contacto');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('cursos_idiomas');
        Schema::dropIfExists('notificaciones');

        Schema::enableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->enum('role', ['Admin', 'Profesor', 'Estudiante']);
            $table->string('token_recuperacion', 255)->nullable();
            $table->dateTime('expiracion_token')->nullable();
            $table->timestamps();
        });

        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('phone', 20)->nullable();
            $table->string('email_personal', 100)->nullable();
            $table->string('email_institucional', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->string('id', 15)->primary();
            $table->string('name', 50);
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('person_id');
            $table->string('language_id', 15);
            $table->enum('status', ['Activo', 'Inactivo'])->default('Activo');
            $table->timestamps();

            $table->foreign('person_id')->references('id')->on('people');
            $table->foreign('language_id')->references('id')->on('languages');
        });

        Schema::create('students', function (Blueprint $table) {
            $table->string('id', 30)->primary();
            $table->unsignedBigInteger('person_id');
            $table->enum('type', ['regular', 'verano']);
            $table->enum('status', ['En proceso', 'Activo', 'Inactivo', 'En prueba'])->default('En proceso');
            $table->string('level', 10)->nullable();
            $table->boolean('is_utp')->default(false);
            $table->timestamps();

            $table->foreign('person_id')->references('id')->on('people');
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 30);
            $table->date('birth_date')->nullable();
            $table->string('home_number', 10)->nullable();
            $table->string('address', 100)->nullable();
            $table->enum('gender', ['Masculino', 'Femenino'])->nullable();
            $table->string('school', 100)->nullable();
            $table->string('signature_path', 255)->nullable();
            $table->string('guardian_id_path', 255)->nullable();
            $table->string('student_id_path', 255)->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students');
        });

        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 30);
            $table->string('father_name', 100)->nullable();
            $table->string('father_workplace', 100)->nullable();
            $table->string('father_work_phone', 20)->nullable();
            $table->string('father_phone', 20)->nullable();
            $table->string('mother_name', 100)->nullable();
            $table->string('mother_workplace', 100)->nullable();
            $table->string('mother_work_phone', 20)->nullable();
            $table->string('mother_phone', 20)->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students');
        });

        Schema::create('student_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 30);
            $table->string('allergies', 255)->default('No');
            $table->string('blood_type', 45);
            $table->string('emergency_name', 100)->nullable();
            $table->string('emergency_phone', 20)->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students');
        });

        Schema::create('groups', function (Blueprint $table) {
            $table->string('id', 10)->primary();
            $table->string('language_id', 15);
            $table->string('level', 10);
            $table->timestamps();

            $table->foreign('language_id')->references('id')->on('languages');
        });

        Schema::create('group_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('group_id', 10);
            $table->unsignedBigInteger('teacher_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('classroom', 50)->nullable();
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('groups');
            $table->foreign('teacher_id')->references('id')->on('teachers');
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 30);
            $table->unsignedBigInteger('group_session_id');
            $table->integer('final_grade')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('group_session_id')->references('id')->on('group_sessions');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 30);
            $table->enum('payment_type', ['PruebaUbicacion', 'Abono']);
            $table->enum('method', ['Caja', 'Banca en Linea']);
            $table->string('bank', 50)->nullable();
            $table->string('account_owner', 100)->nullable();
            $table->string('receipt_path', 255);
            $table->decimal('amount', 8, 2)->default(0.00);
            $table->dateTime('paid_at')->nullable();
            $table->enum('status', ['Pendiente', 'Aceptado', 'Rechazado'])->default('Pendiente');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students');
        });

        Schema::create('balance_movements', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 30);
            $table->enum('movement_type', ['charge', 'payment', 'adjustment']);
            $table->decimal('amount', 8, 2);
            $table->string('reason', 50);
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('group_session_id')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('payment_id')->references('id')->on('payments');
            $table->foreign('group_session_id')->references('id')->on('group_sessions');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 30);
            $table->string('title', 150);
            $table->text('body');
            $table->string('type', 30)->default('general');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('student_id');
            $table->foreign('student_id')->references('id')->on('students');
        });

        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('student_id', 30);
            $table->unsignedBigInteger('group_session_id');
            $table->string('old_level', 10)->nullable();
            $table->string('new_level', 10)->nullable();
            $table->string('approved_by', 100)->nullable();
            $table->timestamp('reverted_at')->nullable();
            $table->string('reverted_by', 100)->nullable();
            $table->timestamps();

            $table->index(['student_id', 'group_session_id']);
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('group_session_id')->references('id')->on('group_sessions');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('balance_movements');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('group_sessions');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('student_contacts');
        Schema::dropIfExists('guardians');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('students');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('people');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();
    }
};
