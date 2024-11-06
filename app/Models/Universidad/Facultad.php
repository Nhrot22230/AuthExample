<?php

namespace App\Models\Universidad;

use Database\Factories\Universidad\FacultadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facultad extends Model
{
    /** @use HasFactory<FacultadFactory> */
    use HasFactory;

    protected $table = 'facultades';

    protected $fillable = [
        'nombre',
        'abreviatura',
        'anexo'
    ];

    public function departamentos(): HasMany
    {
        return $this->hasMany(Departamento::class);
    }

    public function especialidades(): HasMany
    {
        return $this->hasMany(Especialidad::class);
    }
}
