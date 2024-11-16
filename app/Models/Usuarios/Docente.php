<?php

namespace App\Models\Usuarios;

use App\Models\Convocatorias\Convocatoria;
use App\Models\EstudianteRiesgo\EstudianteRiesgo;
use App\Models\Matricula\Horario;
use App\Models\Tramites\TemaDeTesis;
use App\Models\Universidad\Area;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\Seccion;
use Database\Factories\Usuarios\DocenteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Universidad\Curso;

class Docente extends Model
{
    /** @use HasFactory<DocenteFactory> */
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'codigoDocente',
        'tipo',
        'especialidad_id',
        'seccion_id',
        'area_id',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class);
    }

    public function horarios(): BelongsToMany
    {
        return $this->belongsToMany(Horario::class, 'docente_horario');
    }

    public function estudiantesRiesgo(): HasMany
    {
        return $this->hasMany(EstudianteRiesgo::class, 'codigo_docente', 'codigoDocente');
    }

    public function convocatoria(): BelongsToMany
    {
        return $this->BelongsToMany(Convocatoria::class, 'docente_convocatoria');
    }

    public function temasDeTesis() : BelongsToMany
    {
        return $this->belongsToMany(TemaDeTesis::class, 'asesor_tema_tesis', 'docente_id', 'tema_tesis_id');
    }
    public function cursos(): BelongsToMany
    {
    return $this->belongsToMany(Curso::class,'docente_curso','docente_id','curso_id');
    }
}
