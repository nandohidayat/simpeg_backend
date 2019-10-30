<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ruang extends Model
{
    //
    protected $hidden = ['created_at', 'updated_at', 'id'];

    protected $fillable = [
        'ruang'
    ];

    public function bagians()
    {
        return $this->hasMany('App\Bagian');
    }

    public function karyawans()
    {
        return $this->hasMany('App\Karyawan');
    }
}
