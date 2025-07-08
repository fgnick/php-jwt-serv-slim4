<?php
namespace App\Lib;

use App\Lib\Globals;

/**
 * All regex string for comparison.
 * 
 * @author Nick Feng
 * @since 1.0
 */
class StrProc extends Globals
{
    // for regex string test.
    const REGEX_MD5_HASH    = '/^[0-9a-fA-F]{32}$/';
    const REGEX_MD5_HASH_36 = '/^[a-fA-F0-9\-]{36}$';
    
    const REGEX_HOST_NAME      = '/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/';
    const REGEX_HOST_IP        = '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/';
    const REGEX_USER_ID        = '/^[0-9]{1,20}$/';              // number
    const REGEX_NORMAL_NAME    = '/^([\w.\-\s]){2,32}$/';        // number, alphabet, underline, and space.
    const REGEX_INT_FLOAT      = '/^[+-]?\d+(\.\d+)?$/';         // number in int, double, and float
    const REGEX_SPECIAL_CHAR   = '/^[^!-\/:-@\\[-`\\{-~]*$/';    // 不要刪除，很好用
    
    const REGEX_EMAIL_CHAR     = '/^[A-Za-z0-9][\w\-\.]+[A-Za-z0-9]@[A-Za-z0-9]([\w\-\.]+[A-Za-z0-9]\.)+([A-Za-z]){2,4}$/';
    const REGEX_PWD_CHAR       = '/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,16}/';
    const REGEX_OTP_6CHAR      = '/^[A-Za-z0-9]{6}$/';
    
    const REGEX_WORD_ID = '/^[\p{L}\s\-]+$/u'; // 可以更進皆/^[\p{L}\s\-_,\.]+$/u

    const REGEX_PHONE_ZONE_CHAR   = '/^\+?\d{1,10}$/';
    const REGEX_PHONE_ZONE_ALPHA2 = '/^[a-zA-Z]{2}$/';
    
    //const REGEX_POST_ZIPCODE = '/^[-0-9]{3,15}$/';
    
    const REGEX_PHONE_NUM_CHAR_0 = '/^\+?\d+$/';
    const REGEX_PHONE_NUM_CHAR_1 = '/\d?(\s?|-?|\+?|\.?)((\(\d{1,4}\))|(\d{1,3})|\s?)(\s?|-?|\.?)((\(\d{1,3}\))|(\d{1,3})|\s?)(\s?|-?|\.?)((\(\d{1,3}\))|(\d{1,3})|\s?)(\s?|-?|\.?)\d{3}(-|\.|\s)\d{4}/'; 
    const REGEX_PHONE_NUM_CHAR_2 = '/^[0-9]{8,13}$/';
    const REGEX_PHONE_NUM_CHAR_3 = '/^\+?(\d?|\s?|-?)+$/';
    const REGEX_PHONE_NUM_EXT    = '/^\#?[0-9]{1,10}$/';
    
    const REGEX_TAG_CHAR_FILTER = '/[\s!-\,\.-\/:-@\\[-\^`\\{-~]+/'; // for preg_replace();

    const REGEX_FULL_DATETIME  = '/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';
    const REGEX_W3C_DATETIME   = '/([0-2][0-9]{3})\-([0-1][0-9])\-([0-3][0-9])T([0-2][0-9])\:([0-5][0-9])\:([0-5][0-9])(([\-\+]([0-1][0-9])\:([0-5][0-9])))/';
    const REGEX_W3C_TIMEZONE   = '/(([\-\+]([0-1][0-9])\:([0-5][0-9])))/';

    /**
     * SQL Fulltext search special char.
     * @var string
     */
    const FULLTEXT_SPECIAL_CHAR = '/[\s\*\+\-\(\)\<\>\"\'\@\~\#\$\%\^\&\=\\\\\\/]+/';

    /**
     * you can not use it
     */
    private function __construct() {}
}