<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleAssessor extends Model
{
    protected $primaryKey = 'id_schedule_assessor';
    protected $fillable = ['dept', 'assessor'];
    protected $hidden = ['created_at', 'updated_at'];
}
