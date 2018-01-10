<?php
return [
    'id' => 'app-wallet',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'wallet\controllers',
    'components' => [
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'wallet\assets\JsonMessageSource',
                ],
            ],
        ],
    ]
];
