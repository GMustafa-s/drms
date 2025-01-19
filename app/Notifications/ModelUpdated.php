<?php

namespace App\Notifications;

use Illuminate\Database\Eloquent\Model;

class ModelUpdated extends BaseNotification
{
    public function __construct(Model $model, array $changes)
    {
        $modelName = class_basename($model);
        $changedFields = implode(', ', array_keys($changes));
        
        parent::__construct(
            title: "{$modelName} Updated",
            message: "{$modelName} has been updated. Changed fields: {$changedFields}",
            status: 'info',
            actionUrl: $this->getActionUrl($model),
            actionLabel: "View {$modelName}",
            icon: 'heroicon-o-pencil'
        );
    }

    protected function getActionUrl(Model $model): string
    {
        $resource = str_plural(strtolower(class_basename($model)));
        return route("filament.admin.resources.{$resource}.edit", ['record' => $model]);
    }
}
