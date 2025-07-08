<?php

declare(strict_types=1);

namespace Tests\Application\Actions;

use Tests\TestCase;

class UsrAccessActionTest extends TestCase
{
    /**
     * Test to get access token with user name and password.
     * This test will send a POST request to the /auth/access route with a valid user name and password.
     *
     * @return void
     */
    public function testUserAccessTokenRouteResponds()  
    {
        $app = $this->getAppInstance();
        
        $request = $this->sendRequestJson(
            'POST',
            '/api/v1/auth/access',
            [
                'user' => 'youremail@example.com',       // replace with a valid email
                'password' => hash('sha512', 'your password') // sha512 hash of 'password'
            ] 
        );
        $response = $app->handle($request);
        
        $body = (string)$response->getBody();
        $decoded = json_decode($body, true);
        // 輸出收到的資料到 PHPUnit 終端
        fwrite(STDOUT, "\nResponse body:\n" . print_r($decoded, true) . "\n");
        $this->assertEquals(200, $response->getStatusCode(),
            'user access apply error: (' . $response->getStatusCode() . ') ' . $response->getBody()
        );
        return $decoded['data']['token'];
    }

    /**
     * Undocumented function
     *
     * @depends testUserAccessTokenRouteResponds
     */
    public function testUserDataTaking(string $token)
    {
        $app = $this->getAppInstance();
        
        $request = $this->sendRequestJson(
            'GET',
            '/api/v1/usr/data',
            [],
            [ 'X-Token' => $token ]
        );

        $response = $app->handle($request);

        $body = (string)$response->getBody();
        $decoded = json_decode($body, true);
        // 輸出收到的資料到 PHPUnit 終端
        fwrite(STDOUT, "\nResponse body:\n" . print_r($decoded, true) . "\n");

        $this->assertEquals(200, $response->getStatusCode(),
            'user data reading error: (' . $response->getStatusCode() . ') ' . $response->getBody()
        );
    }













}
