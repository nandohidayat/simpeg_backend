<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SIMDataPegawai extends Model
{
    public $timestamps = false;

    protected $table = 'f_data_pegawai';
    protected $primaryKey = 'id_pegawai';
    protected $keyType = 'text';
    protected $hidden = [''];
}
