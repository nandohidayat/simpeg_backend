<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rekan extends Model
{
    //
    protected $fillable = [
        'penilaian_id', 'pegawais_id', 'tingkat'
    ];

    public function penilaians()
    {
        # code...
        return $this->belongsTo('App\Penilaian');
    }

    public function pegawais()
    {
        # code...
        return $this->belongsTo('App\Pegawai')->select(['id', 'nik', 'nama']);
    }
}
