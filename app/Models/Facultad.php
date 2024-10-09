<?php

namespace App\Models;

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
        'anexo',
        'departamento_id',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function especialidades()
    {
        return $this->hasMany(Especialidad::class);
    }
}
