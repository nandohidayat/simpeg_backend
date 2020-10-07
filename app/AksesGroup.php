<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AksesGroup extends Model
{
    protected $primaryKey = 'id_akses_group';
    protected $fillable = ['id_akses_group', 'id_group', 'id_akses', 'status'];
    protected $hidden = ['created_at', 'updated_at'];
}
