<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    //
    protected $hidden = ['created_at', 'updated_at', 'id'];

    protected $fillable = [
        'departemen'
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
