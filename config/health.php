<?php

return [
    /*
     * A result store is responsible for saving the results of the checks.
     */
    'result_stores' => [
        Spatie\Health\ResultStores\EloquentHealthResultStore::class => [
            'model' => Spatie\Health\Models\HealthCheckResult::class,
            'keep_history_for_days' => 5,
        ],
    ],

    /*
     * You can get notified when specific events occur. Out of the box you can use 'mail' and 'slack'.
     */
    'notifications' => [
        /*
         * Notifications will only get sent if this option is set to `true`.
         */
        'enabled' => true,

        'notifications' => [
            Spatie\Health\Notifications\CheckFailedNotification::class => ['mail'],
        ],

        /*
         * Here you can specify the notifiable to which the notifications should be sent.
         * The default notifiable will use the variables specified in this config file.
         */
        'notifiable' => Spatie\Health\Notifications\Notifiable::class,

        /*
         * When checks start failing, you could potentially end up getting
         * a notification every minute.
         *
         * Here you can specify the amount of minutes that should pass
         * between notifications.
         */
        'throttle_notifications_for_minutes' => 60,
    ],

    'checks' => [
        \Spatie\Health\Checks\Checks\UsedDiskSpaceCheck::class => [
            'warning_threshold_percentage' => 70,
            'error_threshold_percentage' => 90,
        ],
        \Spatie\Health\Checks\Checks\DatabaseCheck::class,
        \Spatie\Health\Checks\Checks\DatabaseConnectionCountCheck::class => [
            'warning_threshold' => 75,
            'error_threshold' => 90,
        ],
        \Spatie\Health\Checks\Checks\DebugModeCheck::class,
        \Spatie\Health\Checks\Checks\EnvironmentCheck::class,
        \Spatie\Health\Checks\Checks\HorizonCheck::class,
        \Spatie\Health\Checks\Checks\ScheduleCheck::class,
    ],

    /*
     * You can configure a specific check for a specific environment.
     */
    'environment_specific_checks' => [
        'production' => [
            \Spatie\Health\Checks\Checks\DebugModeCheck::class,
            \Spatie\Health\Checks\Checks\EnvironmentCheck::class,
        ],
    ],
];
