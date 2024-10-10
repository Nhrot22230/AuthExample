<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    /** @use HasFactory<\Database\Factories\InstitucionFactory> */
    use HasFactory;
    
    protected $table = 'instituciones';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'logo',
    ];
}
