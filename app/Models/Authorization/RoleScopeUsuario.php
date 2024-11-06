<?php
namespace App\Models\Authorization;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function scope()
    {
        return $this->belongsTo(Scope::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function entity()
    {
        return $this->morphTo();
    }
}
