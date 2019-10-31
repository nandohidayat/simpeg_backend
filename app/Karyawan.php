<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $primaryKey = 'nik';
    public $incrementing = false;
    protected $hidden = ['created_at', 'updated_at', 'ruang_id', 'departemen_id'];

    //
    protected $fillable = [
        'nik', 'nama', 'departemen_id', 'ruang_id'
    ];

    public function users()
    {
        return $this->hasOne('App\User', 'nik');
    }

    public function bagians()
    {
        return $this->belongsTo('App\Bagian');
    }

    public function departemen()
    {
        return $this->belongsTo('App\Departemen', 'departemen_id')->select("id");
    }

    public function ruang()
    {
        return $this->belongsTo("App\Ruang", 'ruang_id')->select("id");
    }
}
