<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    public function pegawais()
    {
        return $this->hasMany('App\Pegawai');
    }
}
