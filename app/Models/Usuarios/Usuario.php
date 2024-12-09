<?php

namespace App\Models\Usuarios;

use App\Models\Authorization\Role;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Matricula\Horario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
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

    public function roleScopeUsuarios()
    {
        return $this->hasMany(RoleScopeUsuario::class, 'usuario_id')->with('entity');
    }

    public function getScope()
    {
        return $this->roles()->with('scopes')->get()->flatMap->scopes->unique();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notifications::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id')
            ->where('model_type', self::class);
    }

    public function getPermissionsAttribute()
    {
        return $this->roles->flatMap(function ($role) {
            return $role->permissions;
        })->unique('id');
    }

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

    public function docente(): HasOne
    {
        return $this->hasOne(Docente::class);
    }

    public function estudiante(): HasOne
    {
        return $this->hasOne(Estudiante::class);
    }

    public function administrativo(): HasOne
    {
        return $this->hasOne(Administrativo::class);
    }

    public function horarios(): HasManyThrough
    {
        return $this->hasManyThrough(Horario::class, JefePractica::class, 'usuario_id', 'id', 'id', 'horario_id');
    }
    public function jefePracticas(): HasMany
    {
        return $this->hasMany(JefePractica::class, 'usuario_id', 'id');
    }
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        // return [
        //     'roles' => $this->getRoleNames(),
        //     'permissions' => $this->getAllPermissions()->pluck('name'),
        // ];
        return [];
    }

    public function getFullNameAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellido_paterno . ' ' . $this->apellido_materno);
    }
}
