<?php

return [
    'components' => [
        'log' => [
            'traceLevel' => 3,
            'targets' => [
                'fileTarget' => [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['surveybot'],
                    'logFile' => '@app/runtime/logs/surveybot/requests.log',
                    'maxFileSize' => 2 * 1024,
                    'maxLogFiles' => 2,
                ],
            ],
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',
                'username' => 'mobi.tgm',
                'password' => 'Vj,bNuv2017!',
                'port' => '587',
                'encryption' => 'tls',
            ],
        ],
    ],
    'modules' => [
        'debug' => [
            'allowedIPs' => ['*'],
        ],
    ],
];
