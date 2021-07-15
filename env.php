<?php
return [
    'backend' => [
        'frontName' => 'tacodashboard'
    ],
    'crypt' => [
        'key' => '57afae0d568da3346ee5ba5c16af9060'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'db',
                'dbname' => 'magento2',
                'username' => 'magento2',
                'password' => 'magento2',
                'active' => '1',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;'
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'production',
    'session' => [
        'save' => 'files'
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'full_page' => 0,
        'config_webservice' => 1,
        'translate' => 1,
        'compiled_config' => 1,
        'vertex' => 1,
        'google_product' => 1
    ],
    'install' => [
        'date' => 'Fri, 08 Nov 2019 01:27:10 +0000'
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => '850_'
            ],
            'page_cache' => [
                'id_prefix' => '850_'
            ]
        ]
    ],
    'downloadable_domains' => [
        'testnegative.store'
    ]
];
