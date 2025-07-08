<?php

declare(strict_types=1);

namespace App\Model;

use Exception;

use App\Obj\ProcResultObj;
use App\Model\DbServConn;
use App\Model\BaseDbModelWrapper;
use App\Lib\JwtPayload;
use App\Lib\ValueValidate;

use App\Application\Settings\SettingsInterface;

use Psr\Container\ContainerInterface as Container;

/**
 * DbUserAccess class handles user access token operations.
 * This class extends BaseDbModelWrapper to ensure that all methods
 * return a ProcResultObj, providing a consistent response format.
 * 
 * @author Nick Feng
 * @version 1.0
 */
class DbUserAccess extends BaseDbModelWrapper
{
    /**
     * @var DbServConn Database connection instance
     */
    private DbServConn $conn;

    /**
     * @var array Application settings instance
     */
    private array $settings;

    /**
     * Constructor for DbUserAccess.
     * Initializes the database connection using settings from the container.
     * 
     * @param Container $container The container to retrieve settings and services.
     */
    public function __construct( Container $container )
    {
        // Assuming SqlPdo is a singleton or a service in the container
        $this->settings = $container->get(SettingsInterface::class)->get('app');
        $this->conn = new DbServConn($this->settings['database']['main']);
    }
    
    /**
     * Generates an access JWT token for the user.
     * 
     * @param string $username The user's email address.
     * @param string $password The user's password (SHA-512 hash).
     * @param int $lifetime default is 0
     * @return ProcResultObj|array|int Returns a ProcResultObj on success, or an error code on failure.
     */
    protected function genAccessJwt( 
        string $username, 
        string $password,
        int $lifetime = 0,
    ): ProcResultObj|array|int {
        // characters filter
        $username = filter_var( trim( $username ), FILTER_SANITIZE_EMAIL );
        // check format each field.
        if( !ValueValidate::is_email( $username ) ) {
            return static::PROC_INVALID;
        } elseif ( !ValueValidate::is_sha512( $password ) ) {
            return static::PROC_INVALID;
        } elseif ( $lifetime < 0 ) {
            return static::PROC_INVALID;
        }
        
        // if company is disabled, no one can be accessed to in this company.
        $memRow = $this->conn->selectTransact(
            'SELECT m.id, m.pw, m.status 
             FROM member AS m 
             INNER JOIN company AS c ON c.id = m.company_id 
             WHERE m.email = ? AND c.status = 1',
            [ $username ]
        );

        if ( is_int( $memRow ) ) {
            return $memRow;
        } elseif ( empty( $memRow ) || count( $memRow ) !== 1 ) {
            return static::PROC_FAIL;
        }

        $memRow = $memRow[0];
        switch ( $memRow['status'] ) {
            case 1: // user is active
                if ( password_verify( $password, $memRow['pw'] ) ) {
                    // generate a new JWT payload with jti and exp time
                    // if lifetime is 0, it will be set to the default lifetime.
                    $payload = JwtPayload::genPayload( 
                        $this->settings['jwt']['issuer'],
                        'now +' . ( $lifetime > 0 ? $lifetime : '0' ) . ' seconds',
                    );

                    // a new auth code
                    $authcode = JwtPayload::genAuthCode( 
                        $memRow['pw'] . ( $this->settings['jwt']['encrypt_with_browseragent'] ? $_SERVER['HTTP_USER_AGENT'] : '' ) 
                    );

                    // generate a new access token jwt into database
                    $out = $this->conn->writeTransact( 
                        'INSERT INTO oauth_access (id, user_id, auth_code, exp_time) 
                         VALUES(?, ?, ?, FROM_UNIXTIME(?))', 
                        [
                            $payload['jti'], 
                            $memRow['id'], 
                            $authcode, 
                            $payload['exp'] 
                        ]
                    );

                    if ( $out === static::PROC_OK ) {
                        // 10% chance to delete expired access token.
                        if ( mt_rand( 1, 10 ) === 1 ) {
                            $this->conn->writeTransact(
                                'DELETE FROM oauth_access WHERE exp_time < NOW() AND user_id = ?',
                                [ $memRow['id'] ]
                            );
                        }
                        return $payload; // return the new payload with jti, exp, and auth_code
                    } else {
                        return $out; // return error code
                    }
                }
                return static::PROC_FAIL; // password is not matched
            case 2: // user is blocked
                return static::PROC_BLOCKED;
            case 3: // user is not initialized
                return static::PROC_UNINITIALIZED;
            default: // user is active
                return static::PROC_FAIL;
        }
    }

    /**
     * Identify user access token via jti code. it will return the user useful data for other process using. 
     * Otherwise, it will return a error integer of ProcResultCode.
     * 
     * @param string $jti
     * @return ProcResultObj|array|int
     */
    protected function isAccessJwt(string $jti): ProcResultObj|array|int
    {
        if ( empty( $jti ) ) {
            return static::PROC_INVALID;
        }
        
        $out  = static::PROC_FAIL;
        $stmt = NULL;
        try {
            // if company is disabled, no one can be accessed to in this company.
            $stmt = $this->conn->pdo->prepare(
                'SELECT a.user_id, m.company_id, a.auth_code, m.pw 
                 FROM oauth_access AS a 
                 INNER JOIN member AS m ON m.id = a.user_id 
                 INNER JOIN company AS c ON c.id = m.company_id 
                 WHERE a.id = ? AND c.status = 1 AND m.status = 1 AND a.exp_time > NOW()' );
            if ( $stmt->execute( array( $jti ) ) && $stmt->rowCount() === 1 ) {
                $row = $stmt->fetch();
                $auth_code = JwtPayload::genAuthCode( 
                    $row['pw'] . ( $this->settings['jwt']['encrypt_with_browseragent'] ? $_SERVER['HTTP_USER_AGENT'] : '' ) 
                );
                // check the auth code is matched or not.
                if ( hash_equals( $row['auth_code'], $auth_code ) ) {
                    // output user id, user type, and user permission sheet
                    $out = [
                        'id'      => $row['user_id'],
                        'company' => $row['company_id']
                    ];
                }
            }
        } catch ( Exception $e ) {
            $out = $this->conn->sqlExceptionProc( $e );
        }
        if ( $stmt !== NULL ) {
            $stmt->closeCursor();
        }
        return $out;




    }







}

