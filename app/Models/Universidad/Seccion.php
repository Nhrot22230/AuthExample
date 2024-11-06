<?php

namespace App\Models;

use App\Models\Universidad\Departamento;
use App\Models\Usuarios\Docente;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    /** @use HasFactory<\Database\Factories\SeccionFactory> */
    use HasFactory;

    protected $table = 'secciones';

    protected $fillable = [
        'nombre',
        'departamento_id'
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function docentes()
    {
        return $this->hasMany(Docente::class);
    }
}
