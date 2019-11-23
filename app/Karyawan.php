<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $primaryKey = 'nik';
    public $incrementing = false;
    protected $fillable = ['nik', 'nama', 'id_departemen', 'id_ruang'];
    protected $hidden = ['created_at', 'updated_at', 'id_ruang'];

    public function users()
    {
        return $this->hasOne('App\User', 'nik', 'nik');
    }

    public function departemen()
    {
        return $this->belongsTo('App\Departemen', 'id_departemen', 'id_departemen')->select("id_departemen");
    }

    public function ruang()
    {
        return $this->belongsTo("App\Ruang", 'id_ruang', 'id_ruang')->select("id_ruang");
    }

    public function schedules()
    {
        return $this->hasMany("App\Schedule", 'nik', 'nik')->orderBy('tgl', 'asc');
    }
}
