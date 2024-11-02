<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WellUsage extends Model
{

    // Chemical Injection Points Section
    protected $fillable = [
        'company_id',
        'well_id',
        'product_type', // Type of chemical product (e.g., Cor/Scale/FeS Inh)
        'product_name', // Name of the product used for injection
        'injection_location', // Injection location (e.g., Silverton 1H Flowline)
        'ppm', // Parts Per Million for the chemical
        'quarts_per_day', // Usage in quarts per day
        'gallons_per_day', // Usage in gallons per day
        'gallons_per_month', // Usage in gallons per month
        'usage_location', // Location of chemical usage
        'program', // Program under which usage is categorized
        'deliveries_gallons', // Number of deliveries in gallons
        'ppg', // Price per gallon
        'monthly_cost', // Monthly cost based on usage
        'bwe', // BWE value in dollars
        'bowg', // BOWG value in dollars
        'production_location', // Production location (e.g., Silverton 1H)
        'bopd', // Barrels of Oil Per Day (BOPD)
        'mmcf', // Million Cubic Feet (MMCF)
        'bwpd', // Barrels of Water Per Day (BWPD)
        'is_published',
        'created_at'
    ];

//    protected $fillable =[
//        'well_id',
//        'product_name',
//        'product_type',
//        'injection_location',
//        'ppm',
//        'quarts_per_day',
//        'gallons_per_day',
//        'gallons_per_month',
//        'location',
//        'program',
//        'delivery_per_gallon',
//        'ppg',
//        'monthly_cost',
//        'bwe',
//        'bowg',
//        'production_location',
//        'bopd',
//        'is_published'
//    ];
    public function Well(): belongsTo
    {
         return $this->belongsTo(Well::class);
    }
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

}
