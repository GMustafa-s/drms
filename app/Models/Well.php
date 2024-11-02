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
        'rate',
        'based_on',
        'injection_point',
        'comments',
        'site_id',
        'company_id',
        'is_published'
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
    public function wellUsages(): HasMany
    {
        return $this->hasMany(WellUsage::class);
    }
    public function WellUsage(): HasMany
    {
        return $this->hasMany(WellUsage::class);
    }
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

