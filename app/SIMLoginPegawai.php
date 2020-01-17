<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class SIMLoginPegawai extends Authenticatable
{
    use HasApiTokens, Notifiable;

    public $timestamps = false;

    protected $connection = 'pgsql2';
    protected $table = 'login_pegawai';
    protected $primaryKey = 'id_pegawai';
    // protected $hidden = [
    //     'pass_pegawai',
    // ];

    public function findForPassport($username)
    {
        return $this->where('user_pegawai', $username)->first();
    }

    public function validateForPassportPasswordGrant($password)
    {
        return $this->pass_pegawai == md5($password);
    }

    public function getAuthPassword()
    {
        return $this->pass_pegawai;
    }

    // public function setPasswordAttribute($value)
    // {
    //     $this->attributes['password'] = md5($value);
    // }
}
