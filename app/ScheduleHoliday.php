<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleHoliday extends Model
{
    protected $primaryKey = 'id_schedule_holiday';
    protected $fillable = ['tgl', 'keterangan'];
    protected $hidden = ['created_at', 'updated_at'];
}
