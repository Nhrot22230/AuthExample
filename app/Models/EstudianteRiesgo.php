<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstudianteRiesgo extends Model
{
    use HasFactory;

    protected $table = 'estudiante_riesgo'; // Nombre de la tabla en la base de datos

    protected $fillable = [
        'codigo_estudiante',
        'codigo_curso',
        'codigo_docente',
        'horario',
        'codigo_especialidad',
        'riesgo',
        'estado',
        'fecha',
        'desempenho',
        'observaciones',
        'nombre'
    ];

    // Relaciones
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'codigo_estudiante', 'codigoEstudiante');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'codigo_curso', 'id');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'codigo_docente', 'codigoDocente');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'codigo_especialidad', 'id');
    }
}
