<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SIMDepartment extends Model
{
    protected $table = 'f_department';
    protected $primaryKey = 'id_dept';
    protected $keyType = 'String';
    protected $fillable = ['id_dept', 'nm_dept', 'nm_jabatan', 'nm_folder', 'kepala_dept', 'parent_code'];
    protected $hidden = [];

    public function children()
    {
        return $this->hasMany(SIMDepartment::class, 'parent_code', 'id_dept');
    }

    public function parent()
    {
        return $this->belongsTo(SIMDepartment::class, 'id_dept', 'parent_code');
    }

    public function getAllChildren()
    {
        $sections = new Collection();

        foreach ($this->children as $section) {
            $sections->push($section);
            $sections = $sections->merge($section->getAllChildren());
        }

        return $sections;
    }
}
