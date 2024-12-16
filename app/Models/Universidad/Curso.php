<?php

namespace App\Models\Universidad;

use App\Models\EstudianteRiesgo\EstudianteRiesgo;
use App\Models\Matricula\Horario;
use App\Models\Usuarios\Docente;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    use HasFactory;

    protected $fillable = [
        'especialidad_id',
        'seccion_id',
        'cod_curso',
        'nombre',
        'creditos',
        'estado',
        'ct',
        'pa',
        'pb',
        'me',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class);
    }

    public function planesEstudio(): BelongsToMany
    {
        return $this->belongsToMany(PlanEstudio::class, 'plan_estudio_curso');
    }

    public function requisitos(): HasMany
    {
        return $this->hasMany(Requisito::class, 'curso_id', 'id');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class);
    }

    public function estudiantesRiesgo(): HasMany
    {
        return $this->hasMany(EstudianteRiesgo::class, 'codigo_curso', 'id');
    }
    public function docentes(): BelongsToMany
    {
    return $this->belongsToMany(Docente::class,'docente_curso','curso_id','docente_id');
    }
}
