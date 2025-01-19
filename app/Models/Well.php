<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Well extends Model
{
    use SoftDeletes, LogsActivity;

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

    protected $casts = [
        'is_published' => 'boolean',
        'rate' => 'float',
    ];

    protected static function booted()
    {
        static::created(function ($well) {
            try {
                Notification::make('well_created')
                    ->title('Well Created')
                    ->body("Well {$well->lease} has been added to the system")
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                report($e);
            }
        });

        static::updated(function ($well) {
            if ($well->wasChanged('is_published')) {
                try {
                    $status = $well->is_published ? 'published' : 'unpublished';
                    Notification::make('well_status')
                        ->title('Well Status Updated')
                        ->body("Well {$well->lease} has been {$status}")
                        ->warning()
                        ->send();
                } catch (\Exception $e) {
                    report($e);
                }
            }
        });

        static::deleted(function ($well) {
            try {
                Notification::make('well_deleted')
                    ->title('Well Deleted')
                    ->body("Well {$well->lease} has been removed")
                    ->danger()
                    ->send();
            } catch (\Exception $e) {
                report($e);
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
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
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Well has been {$eventName}")
            ->useLogName('well');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function WellUsages(): HasMany
    {
        return $this->hasMany(WellUsage::class);
    }
}
