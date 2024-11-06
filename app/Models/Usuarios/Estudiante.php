<?php

namespace App\Models;

use App\Models\Universidad\Especialidad;
use App\Models\Usuarios\Usuario;
use Database\Factories\EstudianteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Estudiante extends Model
{
    /** @use HasFactory<EstudianteFactory> */
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'codigoEstudiante',
        'especialidad_id',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function horarioEstudiantes(): HasMany
    {
        return $this->hasMany(HorarioEstudiante::class);
    }

    public function horarios(): HasManyThrough
    {
        return $this->hasManyThrough(Horario::class, HorarioEstudiante::class, 'estudiante_id', 'id', 'id', 'horario_id');
    }

    public function estudiantesRiesgo(): HasMany
    {
        return $this->hasMany(EstudianteRiesgo::class, 'codigo_estudiante', 'codigoEstudiante');
    }
}
