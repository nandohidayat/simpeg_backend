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
            $table->integer('type');
            $table->date('mulai');
            $table->date('selesai');
            $table->string('pemohon');
            $table->string('dengan')->nullable();
            $table->integer('status')->default(0);
            $table->string('dept');
            $table->string('kepala');
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
