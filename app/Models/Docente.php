<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    /** @use HasFactory<\Database\Factories\DocenteFactory> */
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'codigoDocente',
        'especialidad_id',
        'area_id',
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
}
