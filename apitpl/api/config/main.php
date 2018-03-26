<?php

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-api',
        ],
        'user' => [
            'identityClass' => 'common\models\Adminuser',
            'enableAutoLogin' => true,
            'enableSession' => false,
            //'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
//         'session' => [
//             // this is the name of the session cookie used for login on the backend
//             'name' => 'advanced-backend',
//         ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],

        */

        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                ['class' => 'yii\rest\UrlRule', 
                    'controller' => 'article',
                    'ruleConfig'=>[
                        'class'=>'yii\web\UrlRule',
                        'defaults'=>[
                            'expand'=>'createdBy',
                        ]
                    ],
                    'extraPatterns'=>[
                        'POST search' => 'search'
                    ],
                ],

                ['class'=>'yii\rest\UrlRule',
                    'controller'=>'top10',
                    'except'=>['delete','create','update','view'],
                    'pluralize'=>false,
                ],

                ['class'=>'yii\rest\UrlRule',
                    'controller'=>'adminuser',
                    'except'=>['delete','create','update','view'],
                    'pluralize'=>false,
                    'extraPatterns' => [
                        'POST login' => 'login',
                    ]
                ],

                ['class'=>'yii\rest\UrlRule',
                    'controller'=>'test',
                    'except'=>['delete','create','update','view'],
                    'pluralize'=>false,
                    'extraPatterns'=>[
                        'GET account_city'  => 'account_city',
                        'GET account_list'  => 'account_list',
                        'GET account_class' => 'account_class',
                        'GET area_list'     => 'area_list',
                        'GET add_user'      => 'add_user',
                        'GET login'         => 'login',
                        'GET logout'        => 'logout',
                        'POST modify_pwd'   => 'modify_pwd',
                        'GET user_zone'     => 'user_zone',
                    ],
                ],

            ],

        ],

        

    ],

    'params' => $params,

];

