<?php

namespace App\Models\Tramites;

use App\Models\Storage\File;
use App\Models\Usuarios\Usuario;
use App\Models\Universidad\Area;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class TemaTesis extends Model
{
    /** @use HasFactory<TemaTesisFactory> */
    use HasFactory;

    protected $table = 'tema_tesis';
    protected $fillable = [
        'titulo',
        'resumen',
        'estado',
        'file_id',
        'file_firmado_id',
        'area_id',
    ];


    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function file_firmado(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function autores(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'autores_tema_tesis',
            'tema_tesis_id',
            'usuario_id'
        );
    }

    public function asesores(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'asesores_tema_tesis',
            'tema_tesis_id',
            'usuario_id'
        );
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function tesis(): HasOne
    {
        return $this->hasOne(Tesis::class);
    }
}
