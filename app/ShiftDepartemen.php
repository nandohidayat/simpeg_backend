<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShiftDepartemen extends Model
{
    protected $primaryKey = 'id_shift_departemen';
    protected $fillable = ['id_shift', 'id_dept', 'status'];
    protected $hidden = ['created_at', 'updated_at'];
}
