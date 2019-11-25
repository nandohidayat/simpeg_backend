<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Absen extends Model
{
    protected $primaryKey = 'id_absen';
    protected $fillable = ['nik', 'type', 'tgl', 'waktu'];
    protected $hidden = ['created_at', 'updated_at'];
}
