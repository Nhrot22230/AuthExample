<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function semestre()
    {
        return $this->belongsTo(Semestre::class);
    }

    public function jefePracticas()
    {
        return $this->hasMany(JefePractica::class);
    }

    public function docentes()
    {
        return $this->belongsToMany(Docente::class, 'docente_horario');
    }

    public function usuarios()
    {
        return $this->hasManyThrough(Usuario::class, JefePractica::class, 'horario_id', 'id', 'id', 'usuario_id');
    }

    public function horarioEstudiantes()
    {
        return $this->hasMany(HorarioEstudiante::class);
    }

    public function estudiantes()
    {
        return $this->hasManyThrough(Estudiante::class, HorarioEstudiante::class, 'horario_id', 'id', 'id', 'estudiante_id');
    }

    public function encuestas()
    {
        return $this->belongsToMany(Encuesta::class, 'encuesta_horario');
    }

}
