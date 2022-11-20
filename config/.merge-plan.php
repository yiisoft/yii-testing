<?php

declare(strict_types=1);

// Do not edit. Content will be replaced.
return [
    '/' => [
        'web' => [
            'yiisoft/error-handler' => [
                'config/web.php',
            ],
            'yiisoft/middleware-dispatcher' => [
                'config/web.php',
            ],
            'yiisoft/yii-event' => [
                'config/web.php',
            ],
            '/' => [
                'web.php',
            ],
        ],
        'common' => [
            'yiisoft/yii-event' => [
                'config/common.php',
            ],
            'yiisoft/log-target-file' => [
                'config/common.php',
            ],
            '/' => [
                'common.php',
            ],
        ],
        'console' => [
            'yiisoft/yii-event' => [
                'config/console.php',
            ],
            'yiisoft/yii-console' => [
                'config/console.php',
            ],
            '/' => [
                'console.php',
            ],
        ],
        'events' => [
            'yiisoft/yii-event' => [
                'config/events.php',
            ],
        ],
        'events-web' => [
            'yiisoft/yii-event' => [
                '$events',
                'config/events-web.php',
            ],
            'yiisoft/log' => [
                'config/events-web.php',
            ],
        ],
        'events-console' => [
            'yiisoft/yii-event' => [
                '$events',
                'config/events-console.php',
            ],
            'yiisoft/yii-console' => [
                'config/events-console.php',
            ],
            'yiisoft/log' => [
                'config/events-console.php',
            ],
        ],
        'params' => [
            'yiisoft/yii-console' => [
                'config/params.php',
            ],
            'yiisoft/log-target-file' => [
                'config/params.php',
            ],
            '/' => [
                'params.php',
            ],
        ],
        'providers-console' => [
            'yiisoft/yii-console' => [
                'config/providers-console.php',
            ],
        ],
    ],
];
