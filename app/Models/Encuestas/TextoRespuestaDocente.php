<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;

class TextoRespuestaDocente extends Model
{
    public function encuestaPregunta() : BelongsTo
    {
        return $this->belongsTo(EncuestaPregunta::class, 'encuesta_pregunta_id');
    }

    public function horario(): BelongsTo {
        return $this->belongsTo(Horario::class);
    }
}
