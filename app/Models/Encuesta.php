<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Encuesta extends Model
{

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'nombre_encuesta',
        'tipo_encuesta',
        'disponible'
    ];

    public function curso(): BelongsToMany {
        return $this->belongsToMany(Curso::class, 'encuesta_curso');
    }

    public function pregunta(): BelongsToMany {
        return $this->belongsToMany(Pregunta::class, 'encuesta_pregunta');
    }
}
