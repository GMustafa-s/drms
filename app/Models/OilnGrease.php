<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OilnGrease extends Model
{
    /** @use HasFactory<\Database\Factories\OilnGreaseFactory> */
    use HasFactory;
    protected $fillable = [
        'company_id',
        'site_id',
        'sample_point',
        'sample_date',
        'o_g_ppm',
        'comments',
    ];
    public function Site(): belongsTo
    {
        return $this->belongsTo(Site::class);
    }
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

}
