<?php

declare(strict_types=1);

namespace App\Application\Actions;

use Exception;

use App\Http\StatusCode;
use App\Http\HttpMessage;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use App\Application\Settings\SettingsInterface;
use RuntimeException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

abstract class Action
{
    protected ContainerInterface $container;

    protected LoggerInterface $logger;

    protected Request $request;

    protected Response $response;

    protected array $appSettings;

    protected array $args;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->appSettings = $container->get(SettingsInterface::class)->get('app');
        $this->logger = $this->container->get(LoggerInterface::class);
    }

    /**
     * 強制規定要在 routes.php 的時候，任何 method 都是像 SampleAction::class 這樣執行，而不是 SampleAction::class . ':sampleMethod'。
     * 如果你要使用自定義的各種分支行為，那這個 magic function 和下面的response的關係，可能要修改一下。
     * 
     * @throws HttpNotFoundException
     * @throws HttpBadRequestException
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->request  = $request;
        $this->response = $response;
        $this->args     = $args;

        try {
            return $this->action();
        } catch (Exception $e) {
            throw new HttpNotFoundException($this->request, $e->getMessage());
        }
    }

    /**
     * @throws Exception
     * @throws HttpBadRequestException
     */
    abstract protected function action(): Response;

    /**
     * If the request method is POST and the Content-Type is either 
     * application/x-www-form-urlencoded or multipart/form-data, 
     * you can retrieve all POST parameters as follows:
     * 
     * $params = (array)$request->getParsedBody();
     * $foo = $params['foo'];
     * 
     * @return array|object
     */
    protected function getFormData()
    {
        $contentType = $this->request->getHeaderLine('Content-Type');
        if (str_contains($contentType, 'application/json')) {
            $input = (string)$this->request->getBody();
            $data = json_decode($input, true);
            return is_array($data) ? $data : [];
        }
        return $this->request->getParsedBody() ?? [];
    }
    
    /**
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name)
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    /**
     * 
     * @param mixed $data   To keep it be flexible data type. I suggest to non-declare it
     * @param int $statusCode
     * @return Response
     * @throws Exception
     */
    protected function respondWithData($data = null, int $statusCode = StatusCode::HTTP_OK): Response
    {
        if ( $statusCode >= StatusCode::HTTP_BAD_REQUEST ) {
            /*$payload = new ActionPayload(
                $statusCode, 
                null, 
                new ActionError( HttpMessage::getMessage($statusCode), is_string($data) ? $data : '' )
            );*/
            $payload = new ActionPayload($statusCode, $data);
        } else {
            $payload = new ActionPayload($statusCode, $data);
        }
        return $this->respond($payload);
    }

    protected function respond(ActionPayload $payload): Response
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $this->response->getBody()->write($json);
        return $this->response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($payload->getStatusCode());
    }

    protected function logString(
        $payloadData, 
        int $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ): string {
        $json = json_encode($payloadData, $options);
        if ($json === false) {
            $error = json_last_error_msg();
            throw new RuntimeException('Failed to encode log string: ' . $error);
        }
        return $json;
    }
}