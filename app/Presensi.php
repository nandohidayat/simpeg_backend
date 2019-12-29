<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = ['pin', 'datetime', 'verified', 'status'];
    protected $hidden = ['created_at', 'updated_at'];
}
