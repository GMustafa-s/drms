<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Well extends Model
{
    protected $fillable = [
        'lease',
        'chemical',
        'chemical_type',
        'ppm',
        'based_on',
        'injection_point',
        'comments',
        'site_id',
        'is_published'
    ];
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
    public function WellUsage(): HasMany
    {
        return $this->hasMany(WellUsage::class);
    }
}

