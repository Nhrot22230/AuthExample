<?php

namespace App\Models\Matricula;

use App\Models\Encuestas\Encuesta;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Semestre;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Estudiante;
use App\Models\Usuarios\JefePractica;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Horario extends Model
{
    use HasFactory;

    protected $fillable = [
        'curso_id',
        'semestre_id',
        'nombre',
        'codigo',
        'vacantes',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class);
    }

    public function jefePracticas()
    {
        return $this->hasMany(JefePractica::class);
    }

    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(Docente::class, 'docente_horario');
    }

    public function usuarios(): HasManyThrough
    {
        return $this->hasManyThrough(Usuario::class, JefePractica::class, 'horario_id', 'id', 'id', 'usuario_id');
    }

    public function horarioEstudiantes()
    {
        return $this->hasMany(HorarioEstudiante::class);
    }

    public function estudiantes(): HasManyThrough
    {
        return $this->hasManyThrough(Estudiante::class, HorarioEstudiante::class, 'horario_id', 'id', 'id', 'estudiante_id');
    }

    public function encuestas(): BelongsToMany
    {
        return $this->belongsToMany(Encuesta::class, 'encuesta_horario');
    }

}
