<?php

namespace App\Models;

use App\Models\Universidad\Curso;
use App\Models\Universidad\Especialidad;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatriculaAdicional extends Model
{
    use HasFactory;

    protected $table = 'matricula_adicionals';

    protected $fillable = [
        'estudiante_id',
        'especialidad_id',
        'motivo',
        'justificacion',
        'estado',
        'motivo_rechazo',
        'curso_id',
        'horario_id'
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class, 'horario_id');
    }
}
