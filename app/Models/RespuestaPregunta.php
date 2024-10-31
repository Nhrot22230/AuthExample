<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RespuestaPregunta extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'id',
        'cant1',
        'cant2',
        'cant3',
        'cant4',
        'cant5',
        'horario_id',
        'jefe_practica_id',
    ];

    public function encuestaPregunta()
    {
        return $this->belongsTo(EncuestaPregunta::class, 'id');
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }

    public function jefePractica()
    {
        return $this->belongsTo(JefePractica::class);
    }
}
