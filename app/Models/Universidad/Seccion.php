<?php

namespace App\Models\Universidad;

use App\Models\Usuarios\Docente;
use Database\Factories\Universidad\SeccionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seccion extends Model
{
    /** @use HasFactory<SeccionFactory> */
    use HasFactory;

    protected $table = 'secciones';

    protected $fillable = [
        'nombre',
        'departamento_id'
    ];

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function docentes(): HasMany
    {
        return $this->hasMany(Docente::class);
    }

    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class);
    }
}
