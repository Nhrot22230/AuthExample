<?php

namespace App\Models\Authorization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $fillable = [
        'name',
        'guard_name',
    ];

    public function scopes(): BelongsToMany
    {
        return $this->belongsToMany(Scope::class, 'role_scopes', 'role_id', 'scope_id');
    }
}