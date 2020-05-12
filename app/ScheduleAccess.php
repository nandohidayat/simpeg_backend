<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleAccess extends Model
{
    protected $primaryKey = 'id_schedule_access';
    protected $fillable = ['dept', 'access', 'assessor'];
    protected $hidden = ['created_at', 'updated_at'];
}
