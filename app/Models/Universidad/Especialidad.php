<?php

namespace App\Models\Universidad;

use App\Models\Encuestas\Encuesta;
use App\Models\EstudianteRiesgo\EstudianteRiesgo;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Estudiante;
use Database\Factories\Universidad\EspecialidadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Especialidad extends Model
{
    /** @use HasFactory<EspecialidadFactory> */
    use HasFactory;

    protected $table = 'especialidades';

    protected $fillable = [
        'nombre',
        'descripcion',
        'facultad_id',
    ];

    public function facultad(): BelongsTo
    {
        return $this->belongsTo(Facultad::class);
    }

    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class);
    }

    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function estudiantesRiesgo(): HasMany
    {
        return $this->hasMany(EstudianteRiesgo::class, 'codigo_especialidad', 'id');
    }
    public function encuestas(): HasMany
    {
        return $this->hasMany(Encuesta::class);

    }

    public function docentes() : HasMany {
        return $this->hasMany(Docente::class);
    }

    public function planEstudios(): HasMany
    {
        return $this->hasMany(PlanEstudio::class);
    }

    /**
     * Obtiene el plan de estudios activo para esta especialidad.
     */
    public function planEstudioActivo(): ?PlanEstudio
    {
        return $this->planEstudios()->where('estado', 'activo')->first();
    }
}
