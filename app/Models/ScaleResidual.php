<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScaleResidual extends Model
{
    protected $fillable = [
        'company_id',
        'well_id',
        'sample_point',
        'sample_date',
        'chem_used',
        'po4',
        'ppm',
        'comments',
    ];
    public function Well(): belongsTo
    {
        return $this->belongsTo(Well::class);
    }
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
