<?php
return [
    'backend' => [
        'frontName' => 'admin123'
    ],
    'queue' => [
        'consumers_wait_for_messages' => 1
    ],
    'crypt' => [
        'key' => 'ps0asx6kxghavy2kzquzru9jsznknbdf'
    ],
    'db' => [
        'table_prefix' => 'mgxo_',
        'connection' => [
            'default' => [
                'host' => 'localhost',
                'dbname' => 'bamboobe_mage216',
                'username' => 'bamboobe_mage216',
                'password' => '9]6S[Xwp80',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
                'driver_options' => [
                    1014 => false
                ]
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'default',
    'session' => [
        'save' => 'db'
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => '0b8_'
            ],
            'page_cache' => [
                'id_prefix' => '0b8_'
            ]
        ]
    ],
    'lock' => [
        'provider' => 'db',
        'config' => [
            'prefix' => ''
        ]
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1,
        'vertex' => 1
    ],
    'downloadable_domains' => [
        'bamboobeee.com'
    ],
    'install' => [
        'date' => 'Tue, 17 Aug 2021 11:25:01 -0600'
    ]
];
