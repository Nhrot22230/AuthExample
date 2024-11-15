<?php

namespace App\Models\Tramites;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcesoAprobacionTema extends Model
{
    use HasFactory;

    protected $table = 'proceso_aprobacion_tema';

    protected $fillable = [
        'tema_tesis_id',
        'fecha_inicio',
        'fecha_fin',
        'estado_proceso',
    ];

    public function temaDeTesis(): BelongsTo {
        return $this->belongsTo(TemaDeTesis::class, 'tema_tesis_id');
    }

    public function estadoAprobacion() : hasMany
    {
        return $this->hasMany(EstadoAprobacionTema::class, 'proceso_aprobacion_id');
    }
}
