<?php

namespace App\Models\Universidad;

use App\Models\Usuarios\Docente;
use Database\Factories\Universidad\AreaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    /** @use HasFactory<AreaFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'especialidad_id',
    ];

    public function docentes(): HasMany
    {
        return $this->hasMany(Docente::class);
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }
}
