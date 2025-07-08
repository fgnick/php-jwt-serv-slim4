<?php
namespace App\Lib;

use ErrorException;

/**
 * GnCookie class provides methods to set cookies for web browsers.
 * 
 * This class is designed to handle cookie management in a way that avoids conflicts with frameworks like Slim or Laravel.
 * It provides two methods: one for setting cookies using the PHP global function `setcookie()`, and another for creating
 * a cookie content string that can be used in response headers.
 * 
 * @author Nick Feng
 * @since 1.0
 */
class GnCookie
{
    private function __construct() {}

    /**
     * set a cookie for browser via PHP global function.
     * 
     * IMPORTANT: Before you use the function, you have to make sure the conflict with the framework never be happened.
     * 
     * @param string $name
     * @param string $content
     * @param string $domain
     * @param string $path
     * @param int    $exp
     * @param bool   $isSecure
     * @param string $sameSite
     * @return bool
     */
    public static function setWebCookie (
        string $name,
        string $content = '',
        string $domain = '',
        string $path = '/',
        int $exp = 0,
        bool $isSecure = false,
        string $sameSite = 'Lax'
    ): bool {
        if (empty($name)) {
            throw new ErrorException('Cookie name cannot be empty!');
        }
        $validSameSiteValues = ['None', 'Lax', 'Strict'];
        if (!in_array($sameSite, $validSameSiteValues, true)) {
            throw new ErrorException('Invalid SameSite value. Allowed values are: None, Lax, Strict.');
        }
        if ($sameSite === 'None' && !$isSecure) {
            throw new ErrorException('SameSite=None requires Secure to be true.');
        }
        $arr_cookie_options = [
            'expires'  => $exp,
            'path'     => $path,
            'domain'   => $domain,       // leading dot for compatibility or use subdomain
            'secure'   => $isSecure,     // or false
            'httponly' => true,          // or false
            'samesite' => $sameSite      // None || Lax  || Strict
        ];
        return setcookie($name, $content, $arr_cookie_options);
    }
    
    /**
     * Create/Remove a cookie for browser.
     * 
     * IMPORTANT: If you use the framework like Slim/Laravel, I suggest you to use the function.
     * Because those frameworks have their own cookie management, 
     * and the PHP global function, setcookie(), may make conflict.
     * So, after you get the cookie content string, you can insert to the response header.
     *
     * @param string $name
     * @param string $content
     * @param string $domain
     * @param string $path
     * @param int $exp When value is 0, it means don't remember me on browser.
     * @param bool $isSecure
     * @param string $sameSite
     * @return string cookie content string. key value will be urlencoded.
     * @throws ErrorException
     */
    public static function setWebCookie_v2 ( 
        string $name, 
        string $content = '', 
        string $domain = '', 
        string $path = '/', 
        int $exp = 0, 
        bool $isSecure = false,
        string $sameSite = 'Lax'
    ): string {
        if ( empty( $name ) ) {
            throw new ErrorException( 'Cookie name cannot be empty!' );
        }
        $validSameSiteValues = ['None', 'Lax', 'Strict'];
        if (!in_array($sameSite, $validSameSiteValues, true)) {
            throw new ErrorException('Invalid SameSite value. Allowed values are: None, Lax, Strict.');
        }
        if ($sameSite === 'None' && !$isSecure) {
            throw new ErrorException('SameSite=None requires Secure to be true.');
        }
        // NOTE: Change SameSite=Strict to SameSite=Lax for email content leading link back to website can be working.
        //       Otherwise, you will lose the Cookies Header when redirecting from other URL or page
        // Set-Cookie: <cookie-name>=<cookie-value>; Expires=<date>; Domain=<domain-value>; Secure; HttpOnly; SameSite=Lax
        return urlencode( $name ) . '=' . ( strlen( $content ) === 0 ? '' : urlencode( $content ) )
               . '; Domain=' . ( strlen( $domain ) ? $domain : $_SERVER['SERVER_NAME'] )
               . '; Path=' . $path
               . ( $exp !== 0 ? '; Expires=' . gmdate( 'D, d-M-Y H:i:s', $exp ) . ' GMT' : '' )
               . ( $isSecure ? '; Secure' : '' )
               . '; HttpOnly; SameSite=' . $sameSite;
    }
}