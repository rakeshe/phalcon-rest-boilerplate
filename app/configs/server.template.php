<?php

return [

    'debug' => true,
    'hostName' => 'http://phalcon-rest.dev',
    'clientHostName' => 'http://phalcon-rest.dev',
    'database' => [

        // Change to your own configuration
        'adapter' => 'Mysql',
        'host' => '127.0.0.1',
        'username' => 'root',
        'password' => 'root',
        'dbname' => 'phalcon_rest_boilerplate',
    ],
    'cors' => [
        'allowedOrigins' => ['*']
    ]
];
