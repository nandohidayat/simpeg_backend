<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AksesDepartemen extends Model
{
    protected $primaryKey = 'id_akses_departemen';
    protected $fillable = ['id_akses', 'id_departemen', 'status'];
    protected $hidden = ['created_at', 'updated_at'];
}
