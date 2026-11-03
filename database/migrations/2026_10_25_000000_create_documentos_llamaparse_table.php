<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_llamaparse', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('archivo');
            $table->string('nombre_original');
            $table->string('mime', 100);
            $table->unsignedBigInteger('tamano')->default(0);
            $table->string('estado', 30)->default('pendiente');
            $table->string('job_id')->nullable()->index();
            $table->string('tier', 30)->nullable();
            $table->unsignedTinyInteger('progreso')->default(0);
            $table->string('progreso_mensaje')->nullable();
            $table->longText('respuesta')->nullable();
            $table->longText('markdown')->nullable();
            $table->longText('items')->nullable();
            $table->string('xlsx_archivo')->nullable();
            $table->longText('json')->nullable();
            $table->longText('json_crudo')->nullable();
            $table->decimal('usage_credits', 8, 2)->nullable();
            $table->text('error')->nullable();
            $table->timestamp('procesado_at')->nullable();
            $table->timestamps();

            $table->index('estado');
            $table->index('user_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_llamaparse');
    }
};
