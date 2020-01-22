<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class SIMLoginPegawai extends Authenticatable implements JWTSubject
{
    use HasApiTokens, Notifiable;

    public $timestamps = false;

    protected $connection = 'pgsql2';
    protected $table = 'login_pegawai';
    protected $primaryKey = 'id_pegawai';
    protected $keyType = 'text';
    protected $hidden = ['pass_pegawai'];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
