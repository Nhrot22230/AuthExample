<?php

namespace App\Models\Universidad;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlanEstudio extends Model
{
    use HasFactory;

    protected $fillable = [
        'cantidad_semestres',
        'especialidad_id',
        'estado',
    ];

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function semestres(): BelongsToMany
    {
        return $this->belongsToMany(Semestre::class, 'plan_estudio_semestre');
    }

    public function cursos(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'plan_estudio_curso')
                    ->withPivot('nivel', 'creditosReq');
    }
}
