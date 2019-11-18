<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AksesKategori extends Model
{
    protected $primaryKey = 'id_akses_kategori';
    protected $fillable = ['kategori'];
    protected $hidden = ['created_at', 'updated_at'];

    public function akses()
    {
        return $this->hasMany('App\Akses', 'id_akses_kategori', 'id_akses_kategori');
    }
}
