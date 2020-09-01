<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProfilPendapatan extends Model
{
    public $timestamps = false;
    protected $table = 'profil_pendapatan';
    protected $primaryKey = 'id_profilp';
}
