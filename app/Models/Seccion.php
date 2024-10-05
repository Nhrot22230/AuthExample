<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    /** @use HasFactory<\Database\Factories\SeccionFactory> */
    use HasFactory;

    protected $table = 'secciones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'codigoSeccion',
        'departamento_id',
        'jefeSeccion_id',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function jefeSeccion()
    {
        return $this->belongsTo(Docente::class, 'jefeSeccion_id');
    }
}
