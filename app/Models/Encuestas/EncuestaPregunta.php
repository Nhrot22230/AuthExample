<?php

namespace App\Models\Encuestas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncuestaPregunta extends Model
{
    //
    use HasFactory;
    protected $table = 'encuesta_pregunta';

    protected $fillable = [
        'encuesta_id',
        'pregunta_id',
        'es_modificacion'
    ];

    public function encuesta(): BelongsTo
    {
        return $this->belongsTo(Encuesta::class);
    }

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class);
    }

    public function respuestasPreguntaDocente()
    {
        return $this->hasOne(RespuestasPreguntaDocente::class, 'respuesta_pregunta_docente_id');
    }

    public function textoRespuestas()
    {
        return $this->hasMany(TextoRespuestaPregunta::class);
    }
}
