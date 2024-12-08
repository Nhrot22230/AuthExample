<?php

namespace App\Models\Authorization;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory;    

    protected $fillable = [
        'name',
        'guard_name',
        'permission_category_id',
    ];

    /**
     * @property PermissionCategory $permission_category
     */
    public function permission_category(): BelongsTo
    {
        return $this->belongsTo(PermissionCategory::class);
    }

    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class);
    }
}
