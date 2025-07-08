<?php

declare(strict_types=1);

namespace App\Application\ResponseEmitter;


use App\Application\Settings\SettingsInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface as Container;
use Slim\ResponseEmitter as SlimResponseEmitter;

/**
 * CORS response emitter middleware:
 * This middleware allows cross-origin requests, which is useful for APIs
 * that may be accessed from different domains or ports.
 * It sets the appropriate headers to allow requests from any origin,
 * and specifies which headers and methods are allowed.
 * 
 * @author Nick Feng
 * @since 1.0
 */
class ResponseEmitter extends SlimResponseEmitter
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
        parent::__construct();
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function emit(ResponseInterface $response): void
    {
        $settings = $this->container->get(SettingsInterface::class);
        if($settings->get('app')['access_cors']['enabled']) {
            $response = $response
                    ->withHeader('Access-Control-Allow-Credentials', (string) $settings->get('app')['access_cors']['supportsCredentials'])
                    ->withHeader('Access-Control-Allow-Origin', implode(',', $settings->get('app')['access_cors']['allowedOrigins']))
                    ->withHeader('Access-Control-Allow-Headers', implode(',', $settings->get('app')['access_cors']['allowedHeaders']))
                    ->withHeader('Access-Control-Allow-Methods', implode(',', $settings->get('app')['access_cors']['allowedMethods'])) 
                    ->withHeader('Access-Control-Expose-Headers', implode(',', $settings->get('app')['access_cors']['exposedHeaders']))
                    ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                    ->withAddedHeader('Cache-Control', 'post-check=0, pre-check=0')
                    ->withHeader('Pragma', 'no-cache');
            // If there is any output in the buffer, clean it
            if (ob_get_contents()) {
                ob_clean();
            }
        }
        parent::emit($response);
    }
}
