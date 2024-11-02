<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    //
    protected $fillable = [
        'name',
        'slug'
    ];

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
