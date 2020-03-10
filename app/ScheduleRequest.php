<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleRequest extends Model
{
    protected $primaryKey = 'id_schedule_request';
    protected $fillable = ['from', 'to', 'tgl', 'status'];
    protected $hidden = ['created_at', 'updated_at'];
}
