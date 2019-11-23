<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    protected $primaryKey = 'id_departemen';
    protected $fillable = ['departemen', 'tingkat', 'id_bagian'];
    protected $hidden = ['created_at', 'updated_at'];

    public function bagians()
    {
        return $this->belongsTo('App\Bagian', 'id_bagian', 'id_bagian');
    }

    public function karyawans()
    {
        return $this->hasMany('App\Karyawan', 'id_departemen', 'id_departemen');
    }

    public function aksesDepartemens()
    {
        return $this->belongsToMany('App\Akses', 'akses_departemens', 'id_departemen', 'id_akses')->where('status', true);
    }

    public function shiftDepartemens()
    {
        return $this->belongsToMany('App\Shift', 'shift_departemens', 'id_departemen', 'id_shift')->where('status', true);
    }
}
