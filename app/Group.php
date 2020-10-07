<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $primaryKey = 'id_group';
    protected $fillable = ['id_group', 'label'];
    protected $hidden = ['created_at', 'updated_at'];
}
