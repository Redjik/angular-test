<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=toptal',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 30
        ],
        'cache' => [
            'class' => \yii\caching\MemCache::className(),
            'useMemcached' => true
        ],

    ]
];
