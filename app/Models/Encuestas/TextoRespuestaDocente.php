<?php

namespace App\Models\Encuestas;

use App\Models\Matricula\Horario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TextoRespuestaDocente extends Model
{
    protected $table = "texto_respuesta_docente";
    protected $fillable = ['horario_id', 'encuesta_pregunta_id', 'respuesta'];
    public function encuestaPregunta() : BelongsTo
    {
        return $this->belongsTo(EncuestaPregunta::class, 'encuesta_pregunta_id');
    }

    public function horario(): BelongsTo {
        return $this->belongsTo(Horario::class);
    }
}
