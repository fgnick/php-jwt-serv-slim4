<?php
namespace App\Lib;

use Exception;
use RuntimeException;

/**
 * Customized random code generator
 * 
 * @author Nick Feng
 * @since 1.0
 */
class GnRandom
{
    /**
     * Generate a random seed depended on different php versions supporting.
     *
     * @param int $length
     * @return string A seed of random.
     * @throws Exception
     */
    private static function randomSeed ( int $length = Globals::RAND_SEED_LEN ): string
    {
        if ( $length <= 8 ) {
            $length = Globals::RAND_SEED_LEN;
        }
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));  // for php 8.2 or higher
        } else if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        } else {
            throw new RuntimeException('please install openssl_random_pseudo_bytes or random_bytes extensions');
        }
    }

    /**
     * Get a 44 char length string from randomSeed().
     *
     * @param string $seed
     * @param int $seed_len
     * @param int $output_len
     * @return string
     * @throws Exception
     */
    public static function genSalt(
        int $output_len = Globals::TOKEN_JTI_LEN,
        int $seed_len = Globals::RAND_SEED_LEN, 
        string $seed = ''
    ): string {
        return substr(strtr(base64_encode(hex2bin(self::randomSeed($seed_len)).'|'.$seed), '+', '.'), 0, $output_len);
    }
}