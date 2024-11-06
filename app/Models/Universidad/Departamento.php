<?php

namespace App\Models\Universidad;

use Database\Factories\Universidad\DepartamentoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departamento extends Model
{
    /** @use HasFactory<DepartamentoFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'facultad_id',
    ];

    public function facultad(): BelongsTo
    {
        return $this->belongsTo(Facultad::class);
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class);
    }
}
