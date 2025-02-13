<?php

namespace App\Models;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use App\Traits\NotifiesAdmins;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    use NotifiesAdmins;

    protected $fillable = [
        'name',
        'company_id',
        'description',
        'is_published'
    ];

//    protected $casts = [
//        'is_published' => 'boolean'
//    ];

//    protected static function booted()
//    {
//        static::created(function ($area) {
//            try {
//                Notification::make('area_created')
//                    ->title('Area Created')
//                    ->body("Area '{$area->name}' has been created")
//                    ->success()
//                    ->send();
//            } catch (\Exception $e) {
//                report($e);
//            }
//        });
//
//        static::updated(function ($area) {
//            try {
//                if ($area->wasChanged('is_published')) {
//                    $status = $area->is_published ? 'published' : 'unpublished';
//                    Notification::make('area_status')
//                        ->title('Area Status Changed')
//                        ->body("Area '{$area->name}' has been {$status}")
//                        ->warning()
//                        ->send();
//                } elseif ($area->wasChanged('name')) {
//                    Notification::make('area_renamed')
//                        ->title('Area Renamed')
//                        ->body("Area has been renamed to '{$area->name}'")
//                        ->info()
//                        ->send();
//                }
//            } catch (\Exception $e) {
//                report($e);
//            }
//        });
//
//        static::deleted(function ($area) {
//            try {
//                Notification::make('area_deleted')
//                    ->title('Area Deleted')
//                    ->body("Area '{$area->name}' has been removed")
//                    ->danger()
//                    ->send();
//            } catch (\Exception $e) {
//                report($e);
//            }
//        });
//    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }


    public function company1()
    {
        return $this->belongsTo(Company::class);
    }

    public function calculateMetric(int $areaId, ?string $filter, string $metric): float
    {
        // Parse the selected month from the filter or default to the current month
        $selectedMonth = $filter ?? now()->format('Y-m');
        $startDate = Carbon::parse($selectedMonth)->startOfMonth();
        $endDate = Carbon::parse($selectedMonth)->endOfMonth();

        // Fetch the area and its associated sites
        $area = $this->find($areaId);
        $sites = $area->sites;

        // Calculate the total based on the metric
        $total = $sites->map(function ($site) use ($startDate, $endDate, $metric) {
            // Fetch wells for the site
            $wells = $site->wells()
                ->with(['wellUsages' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }])
                ->get();

            // Aggregate well usage data
            $wellUsages = $wells->flatMap(fn($well) => $well->wellUsages);

            // Perform metric-specific calculations
            switch ($metric) {
                case 'monthly_cost':
                    return $wellUsages->sum('monthly_cost');
                case 'bwpd':
                    return $wellUsages->sum('bwpd');
                case 'BWE':
                    $monthlyCost = $wellUsages->sum('monthly_cost');
                    $bwpd = $wellUsages->sum('bwpd');
                    return $bwpd > 0 ? round($monthlyCost / ($bwpd * 30.3), 2) : 0;
                case 'BOWG':
                    $monthlyCost = $wellUsages->sum('monthly_cost');
                    $bopdSum = $wellUsages->sum('bopd');
                    $bwpdSum = $wellUsages->sum('bwpd');
                    $mmcfSum = $wellUsages->sum('mmcf');

                    $denominator = ($bopdSum * 30.3) + ($bwpdSum * 30.3) + (($mmcfSum / 6) * 30.3);
                    return $denominator > 0 ? round($monthlyCost / $denominator, 2) : 0;
                default:
                    return 0;
            }
        });

        // Return the summed total for all sites
        return $total->sum() ?? 0;
    }

    // Area-specific metrics with dollar sign
    public function MonthlyCost(int $areaId, ?string $filter): string
    {
        $value = $this->calculateMetric($areaId, $filter, 'monthly_cost');
        return '$' . number_format($value, 2);
    }

    public function Bwpd(int $areaId, ?string $filter): string
    {
        $value = $this->calculateMetric($areaId, $filter, 'bwpd');
        return number_format($value, 2);
    }

    public function BWE(int $areaId, ?string $filter): string
    {
        $value = $this->calculateMetric($areaId, $filter, 'BWE');
        return '$' . number_format($value, 2);
    }

    public function BOWG(int $areaId, ?string $filter): string
    {
        $value = $this->calculateMetric($areaId, $filter, 'BOWG');
        return '$' . number_format($value, 2);
    }

}
