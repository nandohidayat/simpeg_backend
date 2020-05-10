<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $primaryKey = 'id_job';
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = ['color', 'keterangan'];
}
