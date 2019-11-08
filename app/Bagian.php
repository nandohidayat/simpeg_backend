<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bagian extends Model
{
    protected $primaryKey = 'id_bagian';
    protected $fillable = ['bagian'];
    protected $hidden = ['created_at', 'updated_at'];

    public function departemens()
    {
        return $this->hasMany('App\Departemen', 'id_bagian', 'id_bagian');
    }
}
