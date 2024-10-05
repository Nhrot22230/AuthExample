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
    ];

    public function facultades()
    {
        return $this->hasMany(Facultad::class);
    }

    public function secciones()
    {
        return $this->hasMany(Seccion::class);
    }
}
