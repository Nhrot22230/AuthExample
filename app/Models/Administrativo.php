<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrativo extends Model
{
    /** @use HasFactory<\Database\Factories\AdministrativoFactory> */
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'codigoAdministrativo',
        'lugarTrabajo',
        'cargo',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
