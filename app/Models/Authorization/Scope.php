<?php

namespace App\Models\Authorization;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scope extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'entity_type'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_scopes', 'scope_id', 'role_id');
    }
}
