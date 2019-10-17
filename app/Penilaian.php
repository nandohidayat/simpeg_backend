<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Penilaian extends Model
{
    //
    protected $fillable = [
        'pegawais_id', 'mulai', 'selesai'
    ];

    public function rekans()
    {
        # code...
        return $this->hasMany('App\Rekan')->select(['id', 'penilaian_id', 'pegawais_id'])->with('pegawais');
    }

    public function atasans()
    {
        return $this->rekans()->where('tingkat', '=', 1);
    }

    public function setingkats()
    {
        return $this->rekans()->where('tingkat', '=', 2);
    }

    public function bawahans()
    {
        return $this->rekans()->where('tingkat', '=', 3);
    }

    public function pegawais()
    {
        return $this->belongsTo('App\Pegawai')->select(['id', 'nama', 'nik']);
    }
}
