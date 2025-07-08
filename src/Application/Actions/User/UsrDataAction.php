<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Http\StatusCode;
use App\Model\DbUserAccess;
use App\Application\Actions\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface as Container;


class UsrDataAction extends Action
{
    private $userAccess;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->userAccess = new DbUserAccess($container);
    }

    /**
     * 在這裡我不想要只是使用 action 這麼簡單。
     * 我想要把所有跟 user 有關的東西都放在這個 class 之中，作為我管理方便的手段。
     * 
     * {@inheritdoc}
     */
    public function action(): Response
    {
        $jwt_payload = $this->request->getAttribute('jwt');
        // to check if the access token jti is valid, and to get the user id and other information for the next process
        $parsedAccess = $this->userAccess->isAccessJwt($jwt_payload['jti']);
        if ( $parsedAccess->getStatus() === false ) {
            return $this->respondWithData( $parsedAccess, StatusCode::HTTP_UNAUTHORIZED );
        }




        // TODO: 隨個人喜好的開始撰寫其他程式運作 .....

        

        
        return $this->respondWithData($parsedAccess);
    }

}