<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleChange extends Model
{
    protected $primaryKey = 'id_schedule_change';
    protected $fillable = ['tgl', 'type', 'pemohon', 'dengan', 'id_shift', 'status'];
    protected $hidden = ['created_at', 'updated_at'];
}
