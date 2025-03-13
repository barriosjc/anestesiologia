<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNomPadresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_padres', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->char('tipo', 1);
            $table->timestamps();
            $table->softDeletes();
            $table->engine = 'InnoDB';
        });

        Artisan::call('db:seed', [
            '--class' => 'NomPadresSeeder'
        ]);
    }

    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nom_padres');
    }
}
