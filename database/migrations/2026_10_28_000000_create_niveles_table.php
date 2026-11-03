<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('niveles')) {
            return;
        }

        Schema::create('niveles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nivel', 20)->index();
            $table->string('cod_uom', 20)->unique();
            $table->string('descripcion', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('niveles');
    }
};
