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
        'ct',
        'pa',
        'pb',
        'me',
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
        return $this->hasMany(Requisito::class, 'curso_id', 'id');
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

}
