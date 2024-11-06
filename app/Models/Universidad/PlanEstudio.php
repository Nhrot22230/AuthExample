<?php

namespace App\Models;

use App\Models\Universidad\Curso;
use App\Models\Universidad\Especialidad;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanEstudio extends Model
{
    use HasFactory;

    protected $fillable = [
        'cantidad_semestres',
        'especialidad_id',
        'estado',
    ];

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function semestres()
    {
        return $this->belongsToMany(Semestre::class, 'plan_estudio_semestre');
    }

    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'plan_estudio_curso')
                    ->withPivot('nivel', 'creditosReq');
    }
}
