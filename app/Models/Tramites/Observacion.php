<?php

namespace App\Models\Tramites;

use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Observacion extends Model
{
    use HasFactory;

    protected $fillable = [
        'responsable_id',
        'tema_tesis_id',
        'descripcion',
        'estado',
        'fecha',
        'archivo',
    ];

    // Relación con el usuario responsable (belongsTo)
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsable_id');
    }

    // Relación con TemaDeTesis (belongsTo)
    public function temaTesis(): BelongsTo
    {
        return $this->belongsTo(TemaDeTesis::class, 'tema_tesis_id');
    }
}
