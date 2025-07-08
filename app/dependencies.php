<?php

declare(strict_types=1);

use App\Model\SqlPdo;
use App\Application\Settings\SettingsInterface;
use App\Application\Handlers\LoggerMySQLHandler;

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);
            $appSettings = $settings->get('app');
            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);
            try {
                // Create MysqlHandler to save logs to MySQL database
                $pdo = SqlPdo::getInstance( $appSettings['database']['logger'] );
                $mySQLHandler = new LoggerMySQLHandler(
                    $pdo,
                    $appSettings['database']['logger']['table'],
                    [], // if you have additional fields, you can pass them here
                    $loggerSettings['level'],
                    true,
                    false,
                    true
                );
                $logger->pushHandler( $mySQLHandler );
            } catch (\Exception $e) {
                // If MySQL connection fails, fallback to file logging to save on local disk
                $logger->pushProcessor( new WebProcessor() );
                $logger->pushHandler( new StreamHandler( $loggerSettings['path'], $loggerSettings['level'] ) );
            }
            return $logger;
        }
    ]);
};
