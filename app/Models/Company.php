<?php

namespace App\Models;

use App\Traits\NotifiesAdmins;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use NotifiesAdmins;

    //
    protected $fillable = [
        'name',
        'slug'
    ];

    protected static function booted()
    {
        static::created(function ($company) {
            try {
                Notification::make('company_created')
                    ->title('Company Created')
                    ->body("New company '{$company->name}' has been added")
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                report($e);
            }
        });

        static::updated(function ($company) {
            try {
                if ($company->wasChanged('is_published')) {
                    $status = $company->is_published ? 'published' : 'unpublished';
                    Notification::make('company_status')
                        ->title('Company Status Changed')
                        ->body("Company '{$company->name}' has been {$status}")
                        ->warning()
                        ->send();
                } elseif ($company->wasChanged(['name', 'slug'])) {
                    Notification::make('company_details')
                        ->title('Company Details Updated')
                        ->body("Details for '{$company->name}' have been updated")
                        ->info()
                        ->send();
                }
            } catch (\Exception $e) {
                report($e);
            }
        });

        static::deleted(function ($company) {
            try {
                Notification::make('company_deleted')
                    ->title('Company Deleted')
                    ->body("Company '{$company->name}' has been removed")
                    ->danger()
                    ->send();
            } catch (\Exception $e) {
                report($e);
            }
        });
    }

    public function scaleResiduals(): HasMany
    {
        return $this->hasMany(ScaleResidual::class);
    }

    public function FeMnCounts(): HasMany
    {
        return $this->hasMany(FeMnCount::class);
    }
    // In Company model (App\Models\Company)
    public function OilAnalyses()
    {
        return $this->hasMany(OilAnalysis::class); // assuming it's a one-to-many relationship
    }

    public function OilnGreases()
    {
        return $this->hasMany(OilnGrease::class); // assuming it's a one-to-many relationship
    }

    public function GasAnalyses()
    {
        return $this->hasMany(GasAnalysis::class); // assuming it's a one-to-many relationship
    }

    public function areas():HasMany
    {
        return $this->hasMany(Area::class);
    }
    public function sites():HasMany
    {
        return $this->hasMany(Site::class);
    }
    public function wells():HasMany
    {
        return $this->hasMany(Well::class);
    }
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user');
    }
    public function wellUsages():HasMany
    {
        return $this->hasMany(WellUsage::class);
    }
    public function wellusage(): HasMany
    {
        return $this->hasMany(WellUsage::class);
    }
    public function members():belongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
