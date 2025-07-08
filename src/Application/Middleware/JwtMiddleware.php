<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Actions\ActionPayload;
use App\Application\Actions\ActionError;
use App\Application\Settings\SettingsInterface;
use App\Http\StatusCode;
use App\Http\HttpMessage;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Container\ContainerInterface as Container;

use Tuupola\Middleware\JwtAuthentication;
use Tuupola\Middleware\JwtAuthentication\RequestPathRule;
use Tuupola\Middleware\JwtAuthentication\RequestMethodRule;

class JwtMiddleware implements Middleware
{
    private JwtAuthentication $jwtAuthentication;

    /**
     * 
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $settings = $container->get(SettingsInterface::class);
        $this->jwtAuthentication = new JwtAuthentication([
            'header'    => $settings->get('app')['jwt']['header'],               // Header name for the token
            'algorithm' => $settings->get('app')['jwt']['algorithm'],            // Algorithm used to sign the token
            'regexp'    => $settings->get('app')['jwt']['regexp'],
            'secret'    => $settings->get('app')['jwt']['access_secret'],
            'cookie'    => $settings->get('app')['jwt']['access_token_cookie'],  // Cookie name for the token
            'secure'    => $settings->get('app')['jwt']['cookie_secure'],         // Whether the cookie should be sent over HTTPS only
            //'relaxed'  => [ 'localhost', '127.0.0.1' ],  // 您可以列出多個開發服務器以放鬆安全性。 通過以下設置，localhost 和 dev.example.com 都允許傳入未加密(https -> http)的請求。
            'attribute' => 'jwt', // Attribute name to store the JWT token
            'rules' => [
                new RequestPathRule([
                    'path' => '/',
                    'ignore' => [
                        '/hello-world',
                        '/api/v1/auth/access'
                    ] // if you want to skip some path, please add it here. E.g. '/api/v1/your_path'
                ]),
                new RequestMethodRule([
                    'ignore' => ['OPTIONS']
                ])
            ],
            /*
            // official says: Before function is called only when authentication succeeds but before the next incoming middleware is called. 
            // You can use this to alter the request before passing it to the next incoming middleware in the stack. 
            // If it returns anything else than Psr\Http\Message\ServerRequestInterface the return value will be ignored.
            'before' => function ( Request $request, $arguments ) {
                return $request->withAttribute('some thing wnt to pass to the next middleware function', 'anything'); // pass JWT data to controller(Action...之類的)
            },
            */
            /*
            // official says: After function is called only when authentication succeeds and after the incoming middleware stack has been called. 
            // You can use this to alter the response before passing it next outgoing middleware in the stack.
            // If it returns anything else than Psr\Http\Message\ResponseInterface the return value will be ignored.
            'after' => function ( Request $request, $arguments ) {
                return $request->withHeader("X-Brawndo", "plants crave"); // pass JWT data to controller(Action...之類的)
            },
            */
            'error' => function ( Response $response, $arguments ) {
                $error = new ActionError(
                    HttpMessage::getMessage(StatusCode::HTTP_UNAUTHORIZED),
                    $arguments["message"]
                );
                $data = new ActionPayload( StatusCode::HTTP_UNAUTHORIZED, null, $error );
                $response->getBody()->write(
                    json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                );
                return $response->withStatus( StatusCode::HTTP_UNAUTHORIZED )
                                ->withHeader("Content-Type", "application/json");
            }
        ]);
    }

    /**
     * {@inheritdoc}
     * 
     * return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        return $this->jwtAuthentication->process($request, $handler);
    }
}
