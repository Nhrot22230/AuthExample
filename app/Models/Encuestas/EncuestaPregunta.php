<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncuestaPregunta extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'encuesta_id',
        'pregunta_id',
    ];

    public function encuesta()
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
