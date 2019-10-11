<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rekan extends Model
{
    //
    public function penilaians()
    {
        # code...
        return $this->belongsTo('App\Penilaian');
    }
}
