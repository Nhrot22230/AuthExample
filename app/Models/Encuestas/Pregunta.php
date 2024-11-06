<?php

namespace App\Models\Encuestas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pregunta extends Model
{

    use HasFactory;

    protected $fillable = [
        'texto_pregunta',
        'tipo_respuesta',
    ];

    protected $hidden = ['created_at', 'updated_at', 'pivot'];

    public function encuesta(): BelongsToMany {
        return $this->belongsToMany(Encuesta::class, 'encuesta_pregunta');
    }
    public function respuestasPreguntaDocente()
    {
        return $this->hasOne(RespuestasPreguntaDocente::class);
    }
}
