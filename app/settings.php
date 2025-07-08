<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => true, // Should be set to false in production
                'logError'            => false,
                'logErrorDetails'     => false,
                'logger' => [
                    'name' => 'app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],

                /*'errorHandler' => [
                    'displayErrorDetails' => true, // Should be set to false in production
                    'logError'            => false,
                    'logErrorDetails'     => false,
                ],*/
            
                // ------ your database settings ------
                'app' => [
                    'name' => 'YourAppName', // Name of your application, you can set it to your application name
                    'version' => '1.0.0',
                    
                    'access_cors' => [
                        'enabled' => false, // Enable CORS
                        'allowedOrigins' => ['*'], // Allowed origins, you can set it to your domain name or '*' for all origins
                        'allowedMethods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'], // Allowed methods
                        'allowedHeaders' => ['Content-Type', 'Authorization', 'X-Token', 'Accept', 'Origin', 'X-Requested-With'], // Allowed headers. E.g. X-Requested-Wit --> isXhr()
                        'exposedHeaders' => [], // Exposed headers
                        'maxAge'         => 3600, // Max age for preflight requests
                        'supportsCredentials' => true, // Whether to support credentials
                    ],

                    'jwt' => [
                        'enabled'             => true,             // Enable JWT authentication
                        'issuer'              => 'YourIssuerName',  // Issuer of the token, you can set it to your domain name or company name.
                        'algorithm'           => 'HS256',           // HS384
                        'header'              => 'X-Token',
                        'regexp'              => '/(.*)/',
                        'environment'         => 'HTTP_X_TOKEN',
                        'relaxed'             => [], // [ 'localhost', '127.0.0.1' ], 可以列出多個開發服務器以放鬆安全性。 通過以下設置，localhost 和 *.example.com 都允許傳入未加密(https -> http)的請求。
                        'access_token_cookie' => '_ac',     // you can remodify the cookie name
                        'cookie_secure'       => false,     // when set to true, the cookie will only be sent over HTTPS connections
                        'cookie_http_only'    => true,      // when set to true, the cookie will not be accessible via JavaScript
                        'access_secret'       => 'your access secret key string', // this is the secret key used to sign the access token, you can set it to a random string
                        'register_secret'     => 'your register secret key string', // this is the secret key used to sign the register token, you can set it to a random string
                        'pw_reset_secret'     => 'your password reset secret key string', // this is the secret key used to sign the password reset token, you can set it to a random string
                        'api_secret'          => 'your api secret key string have to use access token to get it', // this is the secret key used to sign the API token, you can set it to a random string
                        'internal_access_secret' => 'your internal access secret key string', // this is the secret key used to sign the internal access token, you can set it to a random string
                        'internal_access_header' => 'X-Internal-Access-Token', // header name for internal access token
                        'encrypt_with_browseragent' => false, // Whether to encrypt the token with browser agent

                        'lifetime' => [
                            'default' => 3600,    // Default token lifetime in seconds
                            'access' => 31536000, // Access token lifetime in seconds
                            'register' => 3600,   // Register token lifetime in seconds
                            'pw_reset' => 3600,   // Password reset token lifetime in seconds
                            'api' => 3600, // API token lifetime in seconds
                            'internal_access' => 3600 // Internal access token lifetime in seconds
                        ]
                    ],

                    'database' => [
                        'main' => [
                            'host'     => 'mysql:host=127.0.0.1;port=3306;dbname=mem_access_main;charset=utf8mb4',
                            'user'     => 'your_user_name',
                            'password' => 'your_password',
                            'option'   => [
                                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                                PDO::ATTR_EMULATE_PREPARES   => false,
                                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                                //PDO::MYSQL_ATTR_SSL_KEY  => __DIR__ . '/../security/ssl/client-key.pem',
                                //PDO::MYSQL_ATTR_SSL_CERT => __DIR__ . '/../security/ssl/client-cert.pem',
                                //PDO::MYSQL_ATTR_SSL_CA   => __DIR__ . '/../security/ssl/server-ca.pem',
                                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
                            ]
                        ],

                        'logger' => [
                            'host'     => 'mysql:host=127.0.0.1;port=3306;dbname=sys_log;charset=utf8mb4',
                            'user'     => 'your_user_name',
                            'password' => 'your_password',
                            'table'    => 'logs', // The table name for logs
                            'option'   => [
                                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                                PDO::ATTR_EMULATE_PREPARES   => false,
                                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                                //PDO::MYSQL_ATTR_SSL_KEY  => __DIR__ . '/../security/ssl/log/client-key.pem',
                                //PDO::MYSQL_ATTR_SSL_CERT => __DIR__ . '/../security/ssl/log/client-cert.pem',
                                //PDO::MYSQL_ATTR_SSL_CA   => __DIR__ . '/../security/ssl/log/server-ca.pem',
                                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
                            ]
                        ]
                    ]
                ]
            ]);
        }
    ]);
};
