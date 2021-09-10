<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PendapatanMakan extends Model
{
    protected $table = 'pendapatan_makans';
    protected $primaryKey = 'id_pendapatan_makan';
    protected $fillable = ['tgl', 'pendapatan'];
    protected $hidden = ['created_at', 'updated_at'];
}
