<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{
    use HasFactory, 
    Notifiable, 
    HasRoles, 
    HasPermissions;

    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'estado',
        'picture',
        'google_id',
        'password',
    ];

    protected $hidden = [
        'google_id',
        'password',
        'remember_token',
    ];

    public function docente()
    {
        return $this->hasOne(Docente::class);
    }

    public function estudiante()
    {
        return $this->hasOne(Estudiante::class);
    }

    public function administrativo()
    {
        return $this->hasOne(Administrativo::class);
    }

    public function horarios()
    {
        return $this->hasManyThrough(Horario::class, JefePractica::class, 'usuario_id', 'id', 'id', 'horario_id');
    }
    public function jefePracticas()
    {
        return $this->hasMany(JefePractica::class, 'usuario_id', 'id');
    }
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
        return [
            'roles' => $this->getRoleNames(),
            'permissions' => $this->getAllPermissions()->pluck('name'),
        ];
    }
}