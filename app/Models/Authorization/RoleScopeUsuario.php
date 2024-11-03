<?php

namespace App\Models\Authorization;

use App\Models\Curso;
use App\Models\Departamento;
use App\Models\Especialidad;
use App\Models\Facultad;
use App\Models\Seccion;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleScopeUsuario extends Model
{
    use HasFactory;

    protected $fillable = ['role_id', 'scope_id', 'usuario_id', 'entity_id'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function scope()
    {
        return $this->belongsTo(Scope::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'entity_id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'entity_id');
    }

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'entity_id');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'entity_id');
    }

    public function seccion()
    {
        return $this->belongsTo(Seccion::class, 'entity_id');
    }

    public function entity()
    {
        if (!$this->scope) {
            return $this->especialidad()->whereNull('id'); // Relación vacía
        }

        return match ($this->scope->entity_type) {
            'App\Models\Especialidad' => $this->especialidad(),
            'App\Models\Departamento' => $this->departamento(),
            'App\Models\Facultad' => $this->facultad(),
            'App\Models\Curso' => $this->curso(),
            'App\Models\Seccion' => $this->seccion(),
            default => $this->especialidad()->whereNull('id'), // Relación vacía si no se encuentra una entidad
        };
    }
}
