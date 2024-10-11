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

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function planesEstudio()
    {
        return $this->belongsToMany(PlanEstudio::class, 'plan_estudio_curso');
    }

    public function requisitos()
    {
        return $this->belongsToMany(Curso::class, 'curso_requisito', 'curso_id', 'requisito_id');
    }

    public function esRequisitoDe()
    {
        return $this->belongsToMany(Curso::class, 'curso_requisito', 'requisito_id', 'curso_id');
    }
}
