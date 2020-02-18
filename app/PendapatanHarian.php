<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PendapatanHarian extends Model
{
    protected $table = 'pendapatan_harians';
    protected $primaryKey = 'id_pendapatan_harian';
    protected $fillable = ['tgl', 'pendapatan'];
    protected $hidden = ['created_at', 'updated_at'];
}
