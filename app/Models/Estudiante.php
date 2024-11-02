<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    /** @use HasFactory<\Database\Factories\EstudianteFactory> */
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'codigoEstudiante',
        'especialidad_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function horarioEstudiantes()
    {
        return $this->hasMany(HorarioEstudiante::class);
    }

    public function horarios()
    {
        return $this->hasManyThrough(Horario::class, HorarioEstudiante::class, 'estudiante_id', 'id', 'id', 'horario_id');
    }

    public function estudiantesRiesgo()
    {
        return $this->hasMany(EstudianteRiesgo::class, 'codigo_estudiante', 'codigoEstudiante');
    }
}
