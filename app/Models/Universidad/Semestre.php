<?php

namespace App\Models\Universidad;

use App\Models\Matricula\Horario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semestre extends Model
{
    use HasFactory;

    protected $fillable = [
        'anho',
        'periodo',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class);
    }

    public function planEstudio(): BelongsToMany
    {
        return $this->belongsToMany(PlanEstudio::class, 'plan_estudio_semestre');
    }
}
