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
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(PermissionCategory::class);
    }
}
