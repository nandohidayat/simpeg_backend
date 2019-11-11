<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $primaryKey = 'id_schedule';
    protected $fillable = [
        'nik', 'tgl', 'id_shift', 'masuk', 'keluar'
    ];

    public function karyawans()
    {
        return $this->belongsTo('App\Karyawan', 'nik', 'nik');
    }

    public function shifts()
    {
        return $this->belongsTo('App\Shift', 'id_shift', 'id_shift');
    }
}
