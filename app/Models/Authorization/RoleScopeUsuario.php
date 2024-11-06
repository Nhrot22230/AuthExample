<?php
namespace App\Models\Authorization;

use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RoleScopeUsuario extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'scope_id',
        'usuario_id',
        'entity_type',
        'entity_id'
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
