<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobDepartemen extends Model
{
    protected $primaryKey = 'id_job_departemen';
    protected $fillable = ['id_job', 'id_dept', 'status'];
    protected $hidden = ['created_at', 'updated_at'];
}
