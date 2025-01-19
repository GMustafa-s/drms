<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GasAnalysis extends Model
{
    /** @use HasFactory<\Database\Factories\GasAnalysisFactory> */
    use HasFactory;



    protected $fillable =[
        'well_id',
        'company_id',
        'sample_point',
        'sample_date',
        'co2',
        'h2s_ppm',
        'comments'

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
