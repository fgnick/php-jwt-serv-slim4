<?php
declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Settings\SettingsInterface;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Container\ContainerInterface as Container;

/**
 * 在 App\Application\ResponseEmitter\ResponseEmitter.php 之中，已經有了一個針對所有 response 的 access cors 的一個針對所有 response 的 access cors 的功能在裡面。
 * 這個 CorsMiddleware 是針對所有的 request 來處理 CORS 的。
 * 
 * CORS Middleware:
 * This middleware allows cross-origin requests, which is useful for APIs
 * that may be accessed from different domains or ports.
 * It sets the appropriate headers to allow requests from any origin,
 * and specifies which headers and methods are allowed.
 * 
 * @author Nick
 * @since 1.0
 */
class CorsMiddleware implements Middleware
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * CorsMiddleware constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $settings = $this->container->get(SettingsInterface::class);
        if($settings->get('app')['access_cors']['enabled']) {
            $response = $handler->handle($request);
            $response = $response
                    ->withHeader('Access-Control-Allow-Credentials', (string) $settings->get('app')['access_cors']['supportsCredentials'])
                    ->withHeader('Access-Control-Allow-Origin', implode(',', $settings->get('app')['access_cors']['allowedOrigins']))
                    ->withHeader('Access-Control-Allow-Headers', implode(',', $settings->get('app')['access_cors']['allowedHeaders']))
                    ->withHeader('Access-Control-Allow-Methods', implode(',', $settings->get('app')['access_cors']['allowedMethods'])) 
                    ->withHeader('Access-Control-Expose-Headers', implode(',', $settings->get('app')['access_cors']['exposedHeaders']))
                    ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                    ->withHeader('Pragma', 'no-cache');
            if (ob_get_contents()) {
                ob_clean();
            }
            return $response;
        } else {
            return $handler->handle($request);
        }
    }
}