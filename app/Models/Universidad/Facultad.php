<?php

namespace App\Models;

use App\Models\Universidad\Departamento;
use App\Models\Universidad\Especialidad;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
    /** @use HasFactory<\Database\Factories\FacultadFactory> */
    use HasFactory;

    protected $table = 'facultades';

    protected $fillable = [
        'nombre',
        'abreviatura',
        'anexo'
    ];

    public function departamentos()
    {
        return $this->hasMany(Departamento::class);
    }

    public function especialidades()
    {
        return $this->hasMany(Especialidad::class);
    }
}
