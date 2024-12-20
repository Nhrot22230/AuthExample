<?php

namespace App\Models\Universidad;

use Database\Factories\Universidad\RequisitoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Requisito extends Model
{
    /** @use HasFactory<RequisitoFactory> */
    use HasFactory;

    protected $fillable = [
        'curso_id',
        'curso_requisito_id',
        'plan_estudio_id',
        'tipo',
        'notaMinima',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function cursoRequisito(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function planEstudio(): BelongsTo
    {
        return $this->belongsTo(PlanEstudio::class);
    }
}
