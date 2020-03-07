<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AksesDepartemen extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'akses_departemens';
    protected $primaryKey = 'id_akses_departemen';
    protected $fillable = ['id_akses', 'id_dept', 'status', 'only'];
    protected $hidden = ['created_at', 'updated_at'];
}
