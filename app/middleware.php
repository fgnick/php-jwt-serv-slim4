<?php

declare(strict_types=1);

use App\Application\Middleware\JwtMiddleware;
use App\Application\Settings\SettingsInterface;
use Slim\App;

return function (App $app) {
    // Add JWT Middleware
    if ($app->getContainer()->get(SettingsInterface::class)->get('app')['jwt']['enabled']) {
        $app->add(JwtMiddleware::class);
    }
    // ... Add other middlewares as needed
};
