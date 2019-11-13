<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    protected $primaryKey = 'id_departemen';
    protected $fillable = ['departemen', 'tingkat', 'id_bagian'];
    protected $hidden = ['tingkat', 'id_bagian', 'created_at', 'updated_at'];

    public function bagians()
    {
        return $this->belongsTo('App\Bagian', 'id_bagian', 'id_bagian');
    }

    public function karyawans()
    {
        return $this->hasMany('App\Karyawan', 'id_departemen', 'id_departemen');
    }
}
