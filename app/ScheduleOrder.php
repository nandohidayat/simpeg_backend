<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleOrder extends Model
{
    protected $primaryKey = 'id_schedule_order';
    protected $fillable = ['id_dept', 'order', 'id_pegawai'];
    protected $hidden = ['created_at', 'updated_at'];
}
