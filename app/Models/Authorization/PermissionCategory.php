<?php

namespace App\Models\Authorization;

use App\AccessPath;
use Illuminate\Database\Eloquent\Model;

class PermissionCategory extends Model
{
    protected $fillable = [
        'name',
        'access_path',
    ];

    protected $cast = [
        'access_path' => AccessPath::class,
    ];

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }
}
