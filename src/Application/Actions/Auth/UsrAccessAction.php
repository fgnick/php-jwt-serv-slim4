<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\Action;

use App\Obj\BaseProcResult;
use App\Http\StatusCode;
use App\Lib\ValueValidate;
use App\Model\DbUserAccess;

use Firebase\JWT\JWT;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface as Container;

/**
 * Control a user to apply an access token using username(email) and password
 * 
 * Please don't forget to encode the provided password string to SHA512 first. 
 * This helps protect user accounts.
 * 
 * @author Nick Feng
 * @since 1.0
 */
class UsrAccessAction extends Action
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    /**
     * Apply an user access token via user name and password.
     * This action will return a JWT token if the user is authenticated successfully.
     * If the user is not authenticated, it will return an error message.
     * 
     * {@inheritdoc}
     */
    public function action(): Response
    {
        $formData = $this->getFormData();
        if( empty($formData) || !isset($formData['user']) || !isset($formData['password']) ) {
            return $this->respondWithData(
                BaseProcResult::getCodeText(BaseProcResult::PROC_INVALID),
                statusCode::HTTP_BAD_REQUEST
            );
        } elseif( !is_string($formData['user']) || !is_string($formData['password']) ) {
            return $this->respondWithData(
                BaseProcResult::getCodeText(BaseProcResult::PROC_INVALID),
                statusCode::HTTP_BAD_REQUEST
            );
        } elseif( !ValueValidate::is_email($formData['user']) || !ValueValidate::is_sha512($formData['password']) ) {
            return $this->respondWithData(
                BaseProcResult::getCodeText(BaseProcResult::PROC_INVALID),
                statusCode::HTTP_BAD_REQUEST
            );
        }

        $usrAccess = new DbUserAccess($this->container);
        $jwt_payload = $usrAccess->genAccessJwt(
            $formData['user'],
            $formData['password'],
            3600
        );

        $token = JWT::encode(
            $jwt_payload->getData(),
            $this->appSettings['jwt']['access_secret'],
            $this->appSettings['jwt']['algorithm']
        );

        $this->logger->info('user('.$formData['user'].') access token generated: payload=' . $this->logString($jwt_payload->getData()) );
        
        return $this->respondWithData([ 
            'token' => $token
        ]);
    }
}
