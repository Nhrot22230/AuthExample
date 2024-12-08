<?php

namespace App\Models\Authorization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $fillable = [
        'name',
        'guard_name',
        'scope_id'
    ];

    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class, 'scope_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }
}