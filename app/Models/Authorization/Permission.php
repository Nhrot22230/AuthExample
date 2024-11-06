<?php

namespace App\Models\Authorization;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory;    

    protected $fillable = [
        'name',
        'guard_name',
        'permission_category_id',
    ];

    public function permission_category()
    {
        return $this->belongsTo(PermissionCategory::class);
    }
}
