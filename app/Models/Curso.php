<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;

    protected $fillable = [
        'especialidad_id',
        'cod_curso',
        'nombre',
        'creditos',
        'estado',
    ];

    public function planesEstudio()
    {
        return $this->belongsToMany(PlanEstudio::class, 'plan_estudio_curso')->withPivot('requisito_tipo');
    }

    public function requisitos()
    {
        return $this->belongsToMany(Curso::class, 'curso_requisito', 'curso_id', 'requisito_id')->withPivot('tipo');
    }

    public function esRequisitoDe()
    {
        return $this->belongsToMany(Curso::class, 'curso_requisito', 'requisito_id', 'curso_id')->withPivot('tipo');
    }
}
