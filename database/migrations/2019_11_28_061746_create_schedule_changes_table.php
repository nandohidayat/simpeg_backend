<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_changes', function (Blueprint $table) {
            $table->bigIncrements('id_schedule_change');
            $table->Date('tgl');
            $table->integer('type');
            $table->bigInteger('pemohon');
            $table->bigInteger('dengan')->nullable();
            $table->bigInteger('id_shift')->nullable();
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('schedule_changes');
    }
}
