<?php

namespace App\Traits;

use App\Models\User;
use App\Notifications\ModelCreated;
use App\Notifications\ModelDeleted;
use App\Notifications\ModelUpdated;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

trait NotifiesAdmins
{
    protected static function bootNotifiesAdmins()
    {
        if (!app()->runningInConsole()) {
            static::created(function ($model) {
                if ($model->shouldNotify()) {
                    static::notifyAdmins(new ModelCreated($model));
                }
            });

            static::updated(function ($model) {
                if ($model->shouldNotify() && count($model->getDirty()) > 0) {
                    static::notifyAdmins(new ModelUpdated($model, $model->getDirty()));
                }
            });

            static::deleted(function ($model) {
                if ($model->shouldNotify()) {
                    static::notifyAdmins(new ModelDeleted($model));
                }
            });
        }
    }

    protected static function notifyAdmins($notification)
    {
        $admins = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Super Admin', 'Admin']);
        })->get();

        Notification::send($admins, $notification);
    }

    public function shouldNotify(): bool
    {
        // By default, only notify for published items or when publication status changes
        if (isset($this->attributes['is_published'])) {
            return $this->is_published || $this->isDirty('is_published');
        }
        
        return true;
    }

    public function getModelDisplayName(): string
    {
        $modelName = class_basename($this);
        return Str::title(Str::snake($modelName, ' '));
    }

    public function getNotificationTitle(): string
    {
        $displayName = $this->getModelDisplayName();
        
        if (isset($this->name)) {
            return "{$displayName}: {$this->name}";
        }
        
        if (isset($this->title)) {
            return "{$displayName}: {$this->title}";
        }
        
        if (isset($this->location)) {
            return "{$displayName}: {$this->location}";
        }
        
        if (isset($this->lease)) {
            return "{$displayName}: {$this->lease}";
        }
        
        return $displayName;
    }
}
