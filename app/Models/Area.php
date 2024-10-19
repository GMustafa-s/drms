<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_published'
    ];

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }
    public function company(): BelongsTo
    {
        return $this->belongsTo( Company::class);
    }
}

