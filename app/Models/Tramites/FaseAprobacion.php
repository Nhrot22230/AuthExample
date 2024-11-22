<?php

namespace App\Models\Tramites;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaseAprobacion extends Model
{
    /** @use HasFactory<\Database\Factories\Tramites\FaseAprobacionFactory> */
    use HasFactory;

    protected $table = 'fases_aprobacion';
    protected $fillable = [
        'proceso_aprobacion_id',
        'fase',
        'usuario_id',
        'observacion',
        'file_id',
        'estado_fase',
    ];

    public function procesoAprobacion(): BelongsTo
    {
        return $this->belongsTo(ProcesoAprobacion::class);
    }
}
