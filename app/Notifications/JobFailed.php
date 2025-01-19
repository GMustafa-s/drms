<?php

namespace App\Notifications;

class JobFailed extends BaseNotification
{
    public function __construct(string $jobName, string $error)
    {
        parent::__construct(
            title: "Job Failed: {$jobName}",
            message: "The job failed with error: {$error}",
            status: 'danger',
            icon: 'heroicon-o-exclamation-circle'
        );
    }
}
