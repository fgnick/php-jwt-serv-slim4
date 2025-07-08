<?php

declare(strict_types=1);

use App\Application\Actions\Auth\UsrAccessAction;
use App\Application\Actions\User\UsrDataAction;
use App\Application\Actions\Scheduler\SchedulerAction;
use Slim\Exception\HttpNotFoundException;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    $app->get('/hello-world', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');    // <-- This is a simple test route, the JWT is not required to access this route, and ignores the JWT middleware
        return $response;
    });
    
    $app->group('/api/v1', function (Group $group) {

        $group->group('/auth', function (Group $group) {
        $group->post('/access', UsrAccessAction::class);

            // ... add anything you want in the future
        });

        $group->group('/usr', function(Group $group) {
            $group->get('/data', UsrDataAction::class);

            // ... add anything you want in the future
        });

         // ... add anything you want in the future
    });



    


    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('your home page needs JWT to access, please use the /api/v1/auth/access to get your access token');
        return $response;
    });




    /**
     * Catch-all route to serve a 404 Not Found page if none of the routes match
     * NOTE: make sure this route is defined last
     */
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
        throw new HttpNotFoundException($request);
    });
};
