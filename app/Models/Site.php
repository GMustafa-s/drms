<?php

namespace App\Models;

use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Site extends Model
{ use InteractsWithPageFilters;
    protected $fillable = [
        'location',
        'comments',
        'area_id',
        'company_id',
        'is_published'
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function wells(): HasMany
    {
        return $this->hasMany(Well::class);
    }
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function calculateMetric(int $id, ?string $filter, string $metric): float
    {
        // Parse the selected month from the filter or default to the current month
        $selectedMonth = $filter ?? now()->format('Y-m');
        $startDate = Carbon::parse($selectedMonth)->startOfMonth();
        $endDate = Carbon::parse($selectedMonth)->endOfMonth();

        // Get the current tenant
        $tenant = Filament::getTenant();

        // Fetch sites for the current tenant and specific site ID
        $sites = Site::where('company_id', $tenant->id)->where('id', $id)->get();

        // Calculate the total based on the metric
        $total = $sites->map(function (Site $site) use ($startDate, $endDate, $metric) {
            // Eager load wells and well usages filtered by the date range
            $wells = $site->wells()
                ->with(['wellUsages' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }])
                ->get();

            // Aggregate the well usage data
            $wellUsages = $wells->flatMap(fn($well) => $well->wellUsages);

            switch ($metric) {
                case 'monthly_cost':
                    return $wellUsages->sum('monthly_cost');
                case 'bwpd':
                    return $wellUsages->sum('bwpd');
                case 'BWE':
                    $monthlyCost = $wellUsages->sum('monthly_cost');
                    $bwpd = $wellUsages->sum('bwpd');
                    return $bwpd > 0 ? round($monthlyCost / ($bwpd * 30.3), 2) : 0;
                default:
                    return 0;
            }
        });

        // Return the first value or 0 if no data is found
        return $total->first() ?? 0;
    }

    public function monthlyCost(int $id, ?string $filter): float
    {
        return $this->calculateMetric($id, $filter, 'monthly_cost');
    }

    public function bwpd(int $id, ?string $filter): float
    {
        return $this->calculateMetric($id, $filter, 'bwpd');
    }

    public function BWE(int $id, ?string $filter): float
    {
        return $this->calculateMetric($id, $filter, 'BWE');
    }


}
