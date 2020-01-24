<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'f_department';
    protected $primaryKey = 'id_dept';
    protected $keyType = 'String';
    protected $fillable = ['id_dept', 'nm_dept', 'nm_jabatan', 'nm_folder', 'kepala_dept', 'parent_code'];
    protected $hidden = [];
}
