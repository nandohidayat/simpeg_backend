<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    //
    public function karyawans()
    {
        return $this->belongsTo('App\Karyawan', 'nik', 'nik');
    }

    public function shifts()
    {
        return $this->belongsTo('App\Shift', 'shift_id', 'id');
    }
}
