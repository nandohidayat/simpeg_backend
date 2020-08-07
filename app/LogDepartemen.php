<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogDepartemen extends Model
{
    protected $primaryKey = 'id_log_departemen';
    protected $keyType = 'text';
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = ['id_pegawai', 'type', 'id_dept', 'tgl'];
}
