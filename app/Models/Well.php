<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Well extends Model
{
    protected $fillable = [
        'lease',
        'chemical',
        'chemical_type',
        'rate',
        'based_on',
        'injection_point',
        'comments'
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
