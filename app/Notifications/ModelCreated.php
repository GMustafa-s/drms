<?php

namespace App\Notifications;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ModelCreated extends BaseNotification
{
    public function __construct(Model $model)
    {
        $modelName = class_basename($model);
        $modelId = $model->getKey();
        
        parent::__construct(
            title: "{$modelName} Created",
            message: "A new {$modelName} has been created.",
            status: 'success',
            actionUrl: $this->getActionUrl($model),
            actionLabel: "View {$modelName}",
            icon: 'heroicon-o-plus-circle'
        );
    }

    protected function getActionUrl(Model $model): string
    {
        $tenant = Filament::getTenant();
        $resource = Str::plural(Str::kebab(class_basename($model)));
        
        return route("filament.admin.resources.{$resource}.edit", [
            'tenant' => $tenant,
            'record' => $model
        ]);
    }
}
