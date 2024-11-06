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