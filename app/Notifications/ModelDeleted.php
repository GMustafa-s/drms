<?php

namespace App\Notifications;

use Illuminate\Database\Eloquent\Model;

class ModelDeleted extends BaseNotification
{
    public function __construct(Model $model)
    {
        $modelName = class_basename($model);
        
        parent::__construct(
            title: "{$modelName} Deleted",
            message: "A {$modelName} has been deleted.",
            status: 'danger',
            icon: 'heroicon-o-trash'
        );
    }
}
