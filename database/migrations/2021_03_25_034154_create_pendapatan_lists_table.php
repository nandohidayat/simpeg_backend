<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendapatanListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pendapatan_lists', function (Blueprint $table) {
            $table->bigIncrements('id_pendapatan_list');
            $table->bigInteger('id_pendapatan_profil');
            $table->date('month');
            $table->date('distribution');
            $table->boolean('locked');
            $table->string('title');
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
        Schema::dropIfExists('pendapatan_lists');
    }
}
