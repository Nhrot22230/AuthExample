<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

enum TipoDocente {
    case TPA;
    case TC;
}

class Docente extends Model
{
    /** @use HasFactory<\Database\Factories\DocenteFactory> */
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'codigoDocente',
        'tipo',
        'especialidad_id',
        'seccion_id',
        'area_id',
    ];

    protected $casts = [
        'tipo' => TipoDocente::class,
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function seccion()
    {
        return $this->belongsTo(Seccion::class);
    }
}
