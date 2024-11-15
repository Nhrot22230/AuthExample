<?php

namespace App\Models\Matricula;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioActividad extends Model
{
    use HasFactory;

    // La tabla asociada
    protected $table = 'horario_actividades';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'horario_id',
        'actividad',
        'duracion_semanas',
        'semana_ocurre',
    ];

    // Relación con el Horario
    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class);
    }
}