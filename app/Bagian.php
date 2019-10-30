<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bagian extends Model
{
    //
    protected $fillable = [
        'ruang_id', 'departemen_id'
    ];

    public function ruangs()
    {
        return $this->belongsTo('App\Ruangs');
    }

    public function departemens()
    {
        return $this->belongsTo('App\Departemen');
    }
}
