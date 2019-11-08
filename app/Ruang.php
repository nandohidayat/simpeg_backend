<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ruang extends Model
{
    protected $primaryKey = 'id_ruang';
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = ['ruang'];

    public function karyawans()
    {
        return $this->hasMany('App\Karyawan', 'id_ruang', 'id_ruang');
    }
}
