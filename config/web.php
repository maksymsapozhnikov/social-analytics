<?php

use app\components\RespondentIdentity;

$params = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

$config = [
    'id' => 'respondent-ms',
    'name' => 'TGM:Research',
    'version' => '1.19.3',
    'sourceLanguage' => 'en',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'Africa/Johannesburg',
    'components' => [
        'authClientCollection' => [
            'class'   => \yii\authclient\Collection::class,
            'clients' => [
                'facebook' => [
                    'class' => 'dektrium\user\clients\Facebook',
                    'clientId' => '118859595426684',
                    'clientSecret' => 'b5706b0b9c7ad152c8bd7cab74369956',
                    'attributeNames' => ['name', 'email', 'first_name', 'last_name'],
                ],
            ],
        ],
        'fraudChecker' => [
            'class' => 'app\components\fraud\FraudChecker',
        ],
        'request' => [
            'cookieValidationKey' => 'L9UE-sG-hGwUd2n_XQ1tuszCFVAX9W-u',
        ],
        'response' => [
            'formatters' => [
                'javascript' => 'app\components\formatters\JavascriptFormatter',
            ],
        ],
        'formatter' => [
            'nullDisplay' => '',
            'thousandSeparator' => '',
        ],
        'reCaptcha' => [
            'name' => 'reCaptcha',
            'class' => \himiklab\yii2\recaptcha\ReCaptcha::class,
            'siteKey' => '6LdbBi8UAAAAAOYcAWjgqRhZgxqIQbiqt95plTxi',
            'secret' => '6LdbBi8UAAAAADB-vc40wV8g0NfMjY260cVarnli',
        ],
        'respondentIdentity' => [
            'class' => RespondentIdentity::class,
            'deviceAtlasLicense' => null,
        ],
        'transferTo' => [
            'class' => 'app\components\TransferTo',
            'login' => $params['transfer-to']['login'],
            'token' => $params['transfer-to']['token'],
        ],
        'app_i18n' => [
            'class' => 'yii\i18n\I18N',
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\DbMessageSource',
                    'sourceLanguage' => 'en',
                    'sourceMessageTable'=>'{{%source_message}}',
                    'messageTable'=>'{{%message}}',
                    'enableCaching' => false,
                    //'cachingDuration' => 10,
                    'forceTranslation'=>true,
                ],
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
        ],
        'db' => require(__DIR__ . '/db.php'),
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // new end-points
                '/survey/<sur:\w+>' => '/survey/index',
                '/survey/mobi-app.js' => '/survey/mobi-app',
                '/survey/mobi-app2.js' => '/survey/mobi-app',
                '/go/<rmsid:\w+>' => '/survey/go',
                '/sa/<rmsid:\w+>' => '/survey/alias',
                '/test/transfer-to/<phone:\d+>' => '/test/transfer-to',
                '/rcs/<rmsid:\w+>' => '/recruitment/index',

                // end-point for SurveyGizmo
                // SgSDK API
                'GET /respondent/<rmsid:[\@\w]+>' => '/sg-api/check-respondent',
                'GET /sg-api/check-respondent/<rmsid:[\@\w]+>' => '/sg-api/check-respondent',
                'GET /sg-api/get-response/<rmsid:[\@\w\d]+>' => '/sg-api/get-response',
                'GET /sg-api/post-code/<code:[\w]+>' => '/sg-api/post-code',
                'GET /sg-api/transferto-operators/<code:[\w]+>' => '/sg-api/transferto-operators',
                '/check-phone' => '/sg-api/check-phone',
                '/check-email' => '/sg-api/check-email',

                '/response' => '/site/response',
                '/status/<status:\w+>' => '/site/status',

                // user portal
                '/login' => '/user/security/login',
                '/logout' => '/user/security/logout',
                '/sign-up' => '/user/registration/register',
                '/sign-up/success' => '/dektrium-registration/success',

                // webhook surveybot
                'GET /webhooks/surveybot' => '/surveybot/api/token',
                'POST /webhooks/surveybot' => '/surveybot/api/response',

                '/manage/aliases/rest/<id:\d+>' => '/manage/aliases/rest',
                '/manage/surveys/rest/<id:\d+>' => '/manage/surveys/rest',
            ],
        ],
        'assetManager' => [
            'class' => 'yii\web\AssetManager',
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'js' => [
                        'jquery.min.js'
                    ]
                ],
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => [
                        'css/bootstrap.min.css',
                    ]
                ],
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'js' => [
                        'js/bootstrap.min.js',
                    ]
                ]
            ],
        ],
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@dektrium/user/views' => '@app/views/public/dektrium'
                ],
            ],
        ],
    ],
    'modules' => [
        'manage' => [
            'class' => 'app\modules\manage\Module',
        ],
        'surveybot' => [
            'class' => 'app\modules\surveybot\Module',
            'apiKey' => 'fb12eb27df00d8c24bca6e7f73889739',
        ],
        'gridview' => [
            'class' => 'kartik\grid\Module',
        ],
        'user' => [
            'class' => 'dektrium\user\Module',
            'admins' => ['podroze'],
            'mailer' => [
                'sender' => ['mobi.tgm@gmail.com' => 'Tgm Research'],
            ],
            'enableFlashMessages' => false,
            'enableGeneratingPassword' => true,
            'modelMap' => [
                'LoginForm' => 'app\models\forms\LoginForm',
                'RecoveryForm' => 'app\models\forms\RecoveryForm',
                'RegistrationForm' => 'app\models\forms\RegistrationForm',
                'Profile' => 'app\models\Profile',
            ],
            'controllerMap' => [
                'recovery' => [
                    'class' => 'app\controllers\DektriumRecoveryController',
                    'layout' => '@app/views/layouts/public/index',
                ],
                'registration' => [
                    'class' => 'app\controllers\DektriumRegistrationController',
                    'layout' => '@app/views/layouts/public/index',
                    'on '. \dektrium\user\controllers\RegistrationController::EVENT_AFTER_REGISTER => function($e) {
                        $url = \yii\helpers\Url::to(['/sign-up/success']);

                        return \Yii::$app->controller->redirect($url);
                    },
                ],
                'security' => [
                    'class' => 'app\controllers\DektriumSecurityController',
                    'layout' => '@app/views/layouts/public/index',
                ],
            ],
        ],
        'rbac' => 'dektrium\rbac\RbacWebModule',
    ],
    'params' => $params,
];

return $config;
