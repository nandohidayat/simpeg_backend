<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleChange extends Model
{
    protected $primaryKey = 'id_schedule_change';
    protected $fillable = ['type', 'mulai', 'selesai', 'pemohon', 'dengan', 'status', 'dept', 'kepala'];
    protected $hidden = ['created_at', 'updated_at'];
}
