<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    //
    public function rekans()
    {
        # code...
        return $this->hasMany('App\Rekan');
    }
}
