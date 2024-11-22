<?php

namespace App\Models\Tramites;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Tesis extends Model
{
    /** @use HasFactory<\Database\Factories\Tramites\TesisFactory> */
    use HasFactory;

    protected $fillable = [
        'tema_tesis_id',
    ];


    public function tema_tesis(): BelongsTo
    {
        return $this->belongsTo(TemaTesis::class);
    }
}
