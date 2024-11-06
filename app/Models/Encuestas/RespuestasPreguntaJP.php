<?php

namespace App\Models\Encuestas;

use App\Models\Usuarios\JefePractica;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RespuestasPreguntaJP extends Model
{
    use HasFactory;

    protected $table = 'respuesta_pregunta_jp';

    protected $fillable = [
        'jp_horario_id',
        'encuesta_pregunta_id',
        'cant1',
        'cant2',
        'cant3',
        'cant4',
        'cant5',
    ];

    // Relación con EncuestaPregunta
    public function encuestaPregunta()
    {
        return $this->belongsTo(EncuestaPregunta::class, 'encuesta_pregunta_id');
    }

    // Relación con JP_Horario
    public function jpHorario()
    {
        return $this->belongsTo(JefePractica::class, 'jp_horario_id');
    }
}
