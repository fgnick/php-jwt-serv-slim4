<?php
namespace App\Lib;

use DateTime;
use DateTimeZone;
use Exception;

use App\Obj\JwtPayloadObj;

/**
 * All about JWT payload functions
 * 
 * @author Nick Feng
 * @since 1.0
 */
class JwtPayload extends GnRandom
{
    /**
     * If you want to use the API not only working for user authentication,
     * but also working for others to connect to the service with authentication.
     * You can set the channel name to "app" or other names you want in the JWT payload.
     */
    const PAYLOAD_AUTH_CHANNEL_USR       = 'usr';
    const PAYLOAD_AUTH_CHANNEL_APP       = 'app';   // you can set it to any name you want, if you want to use your JWT token to access the API by another way.
    const PAYLOAD_AUTH_CHANNEL_OUTSOURCE = 'outsource';
    
    /**
     * 為了簡化每次都要透過 new JwtPayloadObj() 來產生 payload 的物件，所以特地用這樣的一個函式簡化產生 payload 的物件。
     * 如果有自己的需求，可以直接使用 JwtPayloadObj 這個物件來產生 payload 的物件。
     *
     * @param string $iss
     * @param string $exp
     * @param array $claims
     * @param string $sub
     * @param string $aud
     * @return array a typical payload object.
     * @throws Exception
     */
    public static function genPayload(
        string $iss,
        string $exp = 'now +1 hours',
        array $claims = [],
        string $sub = '',
        string $aud = ''
    ): array {
        $tz = new DateTimeZone('UTC');    // it's better to sett the time zone to be UTC.
        return (new JwtPayloadObj(
            $iss,
            (new DateTime($exp, $tz))->getTimestamp(), // token expired time
            $sub,
            $aud,
            (new DateTime('now', $tz))->getTimestamp(), // issued at time
            (new DateTime('now', $tz))->getTimestamp(), // not before time
            static::genSalt(),    // unique identifier for the token
            $claims  // customized data. it is good to be an array or an object
        ))->toPayload(); // return the payload array.
    }

    /**
     * Encode a string for login table with browser information and user password.
     *
     * @param string $seed
     * @param string $type
     * @return string a string encoded.
     */
    public static function genAuthCode ( string $seed, string $type = 'md5' ): string
    {
        return hash( $type, $seed );
    }
}