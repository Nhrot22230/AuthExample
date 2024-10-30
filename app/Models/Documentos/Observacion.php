<?php

namespace App\Models\Documentos;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;

class Observacion extends Model
{
    protected $table = 'observaciones';
    protected $fillable = [
        'descripcion',
        'tema_tesis_id',
        'usuario_id',
        'estado',
        'fechaEnvio',
        'documento_url',
    ];

    public function temaTesis()
    {
        return $this->belongsTo(TemaTesis::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
