<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PendapatanProfil extends Model
{
    protected $primaryKey = 'id_pendapatan_profil';
    protected $fillable = ['title', 'view', 'personalia', 'keuangan', 'active'];
    protected $hidden = ['created_at', 'updated_at'];
}
