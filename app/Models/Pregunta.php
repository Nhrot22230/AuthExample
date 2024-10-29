<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pregunta extends Model
{

    protected $fillable = [
        'texto_pregunta',
        'tipo_respuesta',
    ];

    public function encuesta(): BelongsToMany {
        return $this->belongsToMany(Encuesta::class, 'encuesta_pregunta');
    }
}
