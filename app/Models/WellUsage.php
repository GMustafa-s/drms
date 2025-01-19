<?php

namespace App\Models;

use Filament\Notifications\Notification;
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
        // 'usage_location', // Location of chemical usage
        // 'program', // Program under which usage is categorized
        'deliveries_gallons', // Number of deliveries in gallons
        'ppg', // Price per gallon
        'monthly_cost', // Monthly cost based on usage
        'bwe', // BWE value in dollars
        'bowg', // BOWG value in dollars
        // 'production_location', // Production location (e.g., Silverton 1H)
        'bopd', // Barrels of Oil Per Day (BOPD)
        'mmcf', // Million Cubic Feet (MMCF)
        'bwpd', // Barrels of Water Per Day (BWPD)
        'is_published',
        'created_at'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'ppm' => 'float',
        'quarts_per_day' => 'float',
        'gallons_per_day' => 'float',
        'gallons_per_month' => 'float',
        'deliveries_gallons' => 'float',
        'ppg' => 'float',
        'monthly_cost' => 'float',
        'bwe' => 'float',
        'bowg' => 'float',
        'bopd' => 'float',
        'mmcf' => 'float',
        'bwpd' => 'float',
    ];

    protected static function booted()
    {
        static::created(function ($usage) {
            try {
                $well = $usage->well;
                Notification::make('usage_created')
                    ->title('Well Usage Added')
                    ->body("New usage data added for Well {$well->lease}")
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                report($e);
            }
        });

        static::updated(function ($usage) {
            try {
                if ($usage->wasChanged(['monthly_cost', 'bwe', 'bowg'])) {
                    $well = $usage->well;
                    Notification::make('usage_updated')
                        ->title('Well Usage Updated')
                        ->body("Usage metrics updated for Well {$well->lease}")
                        ->info()
                        ->send();
                }
            } catch (\Exception $e) {
                report($e);
            }
        });
    }

    public function well(): BelongsTo
    {
        return $this->belongsTo(Well::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
