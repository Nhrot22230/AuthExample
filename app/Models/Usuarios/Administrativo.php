<?php

namespace App\Models\Usuarios;

use App\Models\Universidad\Facultad;
use Database\Factories\Usuarios\AdministrativoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Administrativo extends Model
{
    /** @use HasFactory<AdministrativoFactory> */
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'codigoAdministrativo',
        'lugarTrabajo',
        'cargo',
        'facultad_id',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function facultad(): BelongsTo
    {
        return $this->belongsTo(Facultad::class);
    }
}
