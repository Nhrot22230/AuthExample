<?php

namespace App\Models\Convocatorias;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GrupoCriterios extends Model
{
    use HasFactory;

    protected $table = 'grupos_criterios';

    protected $fillable = [
        'nombre',
        'obligatorio',
        'descripcion'
    ];

    public function gruposCriterios(): BelongsToMany
    {
        return $this->belongsToMany(Convocatoria::class, 'grupo_criterios_convocatoria');
    }
}
