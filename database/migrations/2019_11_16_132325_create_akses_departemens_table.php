<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAksesDepartemensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('akses_departemens', function (Blueprint $table) {
            $table->bigIncrements('id_akses_departemen');
            $table->bigInteger('id_akses');
            $table->text('id_dept');
            $table->boolean('status');
            $table->boolean('only');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('akses_departemens');
    }
}
