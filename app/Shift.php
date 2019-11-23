<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $primaryKey = 'id_shift';
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = ['mulai', 'selesai', 'kode'];

    public function shiftDepartemens()
    {
        return $this->belongsToMany('App\Shift', 'shift_departemens', 'id_shift', 'id_departemen')->where('status', true);
    }
}
