<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    //
    public function rekans()
    {
        return $this->hasMany('App\Rekans');
    }

    public function penilaians()
    {
        return $this->hasMany('App\Penilaian');
    }
}
