<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scope extends Model
{
    protected $fillable = ['name', 'type', 'related_id'];

    public function related()
    {
        return $this->morphTo();
    }
}
