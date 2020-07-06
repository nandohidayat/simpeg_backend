<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AksesUser extends Model
{
    protected $primaryKey = 'id_akses_user';
    protected $fillable = ['id_akses', 'id_pegawai', 'status'];
    protected $hidden = ['created_at', 'updated_at'];
}
