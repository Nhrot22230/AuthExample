<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextoRespuestaPregunta extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'texto_respuesta',
        'encuesta_pregunta_id',
    ];

    public function encuestaPregunta()
    {
        return $this->belongsTo(EncuestaPregunta::class);
    }
}
