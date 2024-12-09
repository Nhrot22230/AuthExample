<?php

namespace App\Models\Authorization;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scope extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'entity_type', 'category_id'];

    public function category()
    {
        return $this->belongsTo(PermissionCategory::class, 'category_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_scopes', 'scope_id', 'role_id');
    }
}