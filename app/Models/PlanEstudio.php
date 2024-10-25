<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanEstudio extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
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

    public function requisitos()
    {
        return $this->hasMany(Requisito::class);
    }
}
