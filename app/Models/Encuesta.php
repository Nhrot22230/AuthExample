<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Encuesta extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'nombre_encuesta',
        'tipo_encuesta',
        'disponible'
    ];

    public function horario(): BelongsToMany {
        return $this->belongsToMany(Horario::class, 'encuesta_horario');
    }

    public function pregunta(): BelongsToMany {
        return $this->belongsToMany(Pregunta::class, 'encuesta_pregunta');
    }
}
