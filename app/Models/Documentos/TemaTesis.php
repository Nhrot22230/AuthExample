<?php

namespace App\Models\Documentos;

use App\Models\Docente;
use App\Models\Especialidad;
use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemaTesis extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'resumen',
        'documento_url',
        'estado',
        'especialidad_id',
        'estadoJurado',
        'fechaEnvio',
    ];

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function observaciones()
    {
        return $this->hasMany(Observacion::class);
    }

    public function jurados()
    {
        return $this->belongsToMany(Docente::class, 'tema_tesis_jurado')
            ->withPivot('estado');
    }

    public function asesores()
    {
        return $this->belongsToMany(Docente::class, 'tema_tesis_asesor');
    }

    public function autores()
    {
        return $this->belongsToMany(Estudiante::class, 'tema_tesis_autor');
    }
}
