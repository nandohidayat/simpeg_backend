<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    //
    public function users()
    {
        return $this->hasOne('App\User');
    }

    public function rekans()
    {
        return $this->hasMany('App\Rekans');
    }

    public function penilaians()
    {
        return $this->hasMany('App\Penilaian');
    }

    public function jabatans()
    {
        return $this->belongsTo('App\Jabatan');
    }
}
