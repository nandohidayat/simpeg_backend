<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogDepartemensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_departemens', function (Blueprint $table) {
            $table->bigIncrements('id_log_departemen');
            $table->string('id_pegawai');
            $table->string('id_dept');
            $table->date('masuk')->nullable();
            $table->date('keluar')->nullable();
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
        Schema::dropIfExists('log_departemens');
    }
}
