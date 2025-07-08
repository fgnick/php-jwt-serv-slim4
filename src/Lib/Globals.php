<?php
namespace App\Lib;

/**
 * 所有全域使用的常數都在此
 * 
 * @author Nick Feng
 * @since 1.0
 */
class Globals
{
    const TOKEN_JTI_LEN = 44;
    const RAND_SEED_LEN = 32;
    const SHA512_PW_LEN = 128;

    const USER_UID_LEN      = 32;
    const USER_NICKNAME_LEN = 100;

    const COMPANY_UID_LEN   = 32;
    const COMPANY_NAME_LEN  = 100;

    //const APP_UID_LEN = 36; // 標準 uuid->v0
    const APP_HOSTNAME_LEN   = 100;
    const APP_SECRET_LEN_MAX = 100;
    const APP_SECRET_LEN_MIN = 32;

    const SQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

    const STR_ADDR_ZIPCODE_LEN = 16;
    const STR_ADDR_STATE_LEN   = 100;
    const STR_ADDR_CITY_LEN    = 100;
    const STR_ADDR_01_LEN      = 1024;
    const STR_ADDR_02_LEN      = 1024;

    const DEFAULT_COOKIE_LIFETIME = 90 * 24 * 60 * 60; // 90 天

    /**
     * Datetime string format for SQL.
     * 
     * @var string
     */
    const SQL_DATE_FORMAT = 'Y-m-d H:i:s';
    
    /**
     * Invitation expired time default is 1 week.
     * NOTE: you can change it to want you want, but please make sure the time format is correct.
     *
     * @var string
     */
    const INVITATION_EXP_TIME = 'now +168 hours';   // 7 days

    const ACCESS_TIME_REMEMBER_ME   = 'now +1 years';
    const ACCESS_TIME_UNREMEMBER_ME = 'now +24 hours';
    const ACCESS_TIME_API_DEFAULT   = 'now +24 hours';

    /**
     * you can not use it
     */
    private function __construct() {}
}