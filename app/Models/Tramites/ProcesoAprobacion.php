<?php

namespace App\Models\Tramites;

use App\Models\Storage\File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcesoAprobacion extends Model
{
    /** @use HasFactory<\Database\Factories\Tramites\ProcesoAprobacionFactory> */
    use HasFactory;

    protected $table = 'procesos_aprobacion';
    protected $fillable = [
        'fases_aprobadas',
        'total_fases',
        'titulo',
        'resumen',
        'file_id',
        'tema_tesis_id',
        'estado_proceso',
    ];

    public function temaTesis(): BelongsTo
    {
        return $this->belongsTo(TemaTesis::class);
    }

    public function fases(): HasMany
    {
        return $this->hasMany(FaseAprobacion::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
