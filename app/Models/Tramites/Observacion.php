<?php

namespace App\Models;

use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Observacion extends Model
{
    use HasFactory;

    protected $fillable = [
        'responsable_id',
        'tema_tesis_id',
        'descripcion',
        'estado',
        'fecha',
        'archivo',
    ];

    // Relación con el usuario responsable (belongsTo)
    public function responsable()
    {
        return $this->belongsTo(Usuario::class, 'responsable_id');
    }

    // Relación con TemaDeTesis (belongsTo)
    public function temaTesis()
    {
        return $this->belongsTo(TemaDeTesis::class, 'tema_tesis_id');
    }
}
