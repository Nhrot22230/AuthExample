<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    /** @use HasFactory<\Database\Factories\DepartamentoFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'facultad_id',
    ];

    public function facultad()
    {
        return $this->belongsTo(Facultad::class);
    }

    public function secciones()
    {
        return $this->hasMany(Seccion::class);
    }
}
