<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semestre extends Model
{
    use HasFactory;

    protected $fillable = [
        'anho',
        'periodo',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function planEstudio()
    {
        return $this->belongsToMany(PlanEstudio::class, 'plan_estudio_semestre');
    }
}

// Semestre: 2024-1, 1 enero, 5 julio

// Director de carrera crea un semestre -> no es precisa



// TODO: Curso y semestre (? 


// Gestion de plan de estudios
// Planes de estudio -> prototipos 
// Director de carrera -> asigna cursos a estos prototipos
// Director de carrera -> asigna un requisitos a estos prototipos
// Director de carrera -> asigna un semestre a estos prototipos


