<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Akses extends Model
{
    protected $primaryKey = 'id_akses';
    protected $fillable = ['id_akses_kategori', 'akses'];
    protected $hidden = ['created_at', 'updated_at'];

    public function aksesKategoris()
    {
        return $this->belongsTo('App\AksesKategori', 'id_akses_kategori', 'id_akses_kategori');
    }

    public function aksesDepartemens()
    {
        return $this->belongsToMany('App\Departemen', 'akses_departemens', 'id_akses', 'id_departemen');
    }
}
