<?php

namespace App\Models\Authorization;

use App\AccessPath;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermissionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'access_path',
    ];

    protected array $cast = [
        'access_path' => AccessPath::class,
    ];

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }
}
