<?php

namespace App\Models\Encuestas;

use App\Models\Matricula\Horario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RespuestasPreguntaDocente extends Model
{
    use HasFactory;

    protected $table = 'respuesta_pregunta_docente';

    protected $fillable = [
        'horario_id',
        'encuesta_pregunta_id',
        'cant1',
        'cant2',
        'cant3',
        'cant4',
        'cant5',
    ];

    public function encuestaPregunta()
    {
        return $this->belongsTo(EncuestaPregunta::class, 'encuesta_pregunta_id');
    }

    // Relación con Horario
    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class);
    }
}
