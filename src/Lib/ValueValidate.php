<?php
namespace App\Lib;

use DateTime;

/**
 * Basic functions for SQL
 *
 * @author Nick
 * @since 1.0
 */
class ValueValidate extends StrProc
{
    public static function is_hashMd5( string $str ): bool
    {
        return (bool)preg_match('/^[a-f0-9]{32}$/i', $str);
    }   

    /**
     *
     * @param string $value
     * @return bool
     */
    public static function is_token_jti(string $value): bool
    {
        return self::safeStrlen( $value ) === static::TOKEN_JTI_LEN;
    }

    /**
     * check host name(not IP)
     * @param string $value
     * @return bool
     */
    public static function is_hostname(string $value): bool
    {
        return preg_match(static::REGEX_HOST_NAME, $value) || self::is_ip($value) || $value === 'localhost' ;
    }

    /**
     * check it is an IP string
     *
     * @param string $value
     * @return bool
     */
    public static function is_ip(string $value): bool
    {
        return (bool)preg_match(static::REGEX_HOST_IP, $value);
    }

    /**
     * check user uid is via GnRandom.php
     *
     * @param string $value
     * @return bool
     */
    public static function is_user_uid( string $value ): bool
    {
        return self::safeStrlen( $value ) === static::USER_UID_LEN;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function is_user_nickname( string $value ): bool
    {
        return self::safeStrlen( $value ) <= static::USER_NICKNAME_LEN;
    }

    /**
     * @param int $value
     * @return bool
     */
    public static function is_user_status( int $value ): bool
    {
        return $value >= 0 && $value <= 2;
    }

    /**
     * @param int $value
     * @return bool
     */
    public static function is_user_type( int $value ): bool
    {
        return $value >= 1 && $value <= 6;
    }

    /**
     * check company uid string via Uuid.php
     *
     * @param string $value
     * @return bool
     */
    public static function is_company_uid( string $value ): bool
    {
        return self::safeStrlen( $value ) === static::COMPANY_UID_LEN;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function is_company_name( string $value ): bool
    {
        return self::safeStrlen( $value ) <= static::COMPANY_NAME_LEN;
    }

    /**
     * @param int $value
     * @return bool
     */
    public static function is_company_status( int $value ): bool
    {
        return $value >= 0 && $value <= 2;
    }

    public static function is_app_uid(string $value): bool
    {
        return Uuid::is_valid( $value );
    }

    public static function is_app_secretkey( string $value ): bool
    {
        $len = self::safeStrlen( $value );
        return $len >= static::APP_SECRET_LEN_MIN && $len <= static::APP_SECRET_LEN_MAX;
    }

    /**
     * 除了檢查字串長度，只允許各國文字、空格、連結號(-)
     * @param string $value
     * @return bool
     */
    public static function is_app_issuer(string $value): bool
    {
        return self::safeStrlen( $value ) > 0 &&
               self::safeStrlen( $value ) <= static::APP_HOSTNAME_LEN &&
               preg_match(static::REGEX_WORD_ID, $value);
    }

    /**
     * @param int $value
     * @return bool
     */
    public static function is_app_status( int $value ): bool
    {
        return !($value !== 0 && $value !== 1);
    }

    public static function is_sha512( string $str ): bool
    {
        return (bool)preg_match('/^[a-f0-9]{128}$/i', $str);
    }

    public static function is_email( string $str ): bool
    {
        return (bool)preg_match(self::REGEX_EMAIL_CHAR, $str);
    }
    
    /**
     * It is more safe to get string length between strlen() and mb_strlen().
     * @param string $s
     * @return int Return false for fail, or number for string length(in utf-8).
     */
    public static function safeStrlen( string $s ): int
    {
        if (!function_exists('mb_detect_encoding')) {
            return strlen($s);
        }
        if (false === $encoding = mb_detect_encoding($s)) {
            return strlen($s);
        }
        if (!function_exists('mb_strlen')) {
            return strlen($s) ;
        }
        return mb_strlen($s, $encoding);
    }
    
    /**
     * It is more safe to get substring substr() and mb_substr().
     * @param string $s String to deal with.
     * @param int $startPos start position.
     * @param int|null $len length for substring.
     * @return string Empty is fail.
     */
    public static function safeSubstr (string $s, int $startPos = 0, ?int $len = NULL): string
    {
        if (!function_exists('mb_detect_encoding')) {
            return substr($s, $startPos, $len);
        }
        if (false === $encoding = mb_detect_encoding($s)) {
            return substr($s, $startPos, $len);
        }
        if (!function_exists('mb_substr')) {
            return substr($s, $startPos, $len) ;
        }
        return mb_substr($s, $startPos, $len, $encoding) ;
    }
    
    /**
     * Check country code in alpha 2 or alpha 3.
     * 
     * @param string $alp
     * @return int
     */
    public static function isCountryAlpha ( string $alp ): int
    {
        return preg_match( '/^[a-zA-Z]{2,3}$/', $alp );
    }

    // ============================= for general data checking =============================[start]

    public static function isIntArray( array $arr ): bool
    {
        foreach ( $arr as $v ) {
            if ( !is_int( $v ) ) {
                return false;
            }
        }
        return !empty($arr);
    }

    public static function isUnsignedInt( array &$arr, bool $toInt = false ): bool
    {
        foreach ( $arr as &$v ) {
            if ( ctype_digit( $v ) ) {
                if ( $toInt ) {
                    $v = (int)$v;
                }
                continue;
            } else if ( is_int( $v ) ) {
                if ( $v >= 0 ) {
                    continue;
                }
            }
            return false;
        }
        unset( $v );
        return !empty($arr);
    }

    public static function isNumberArray( array &$arr, bool $toInt = false ): bool
    {
        foreach ( $arr as &$v ) {
            if ( !is_numeric( $v ) ) {
                return false;
            }
            if ( $toInt ) {
                $v = (int)$v;
            }
        }
        unset( $v );
        return !empty($arr);
    }

    public static function uniqueArray( array $arr ): array
    {
        return array_values( array_unique( $arr ) );
    }

    public static function isArrayKeysEqual( array $a1, array $a2 ): bool
    {
        return !array_diff_key( $a1, $a2 ) && !array_diff_key( $a2, $a1 );
    }

    public static function isUuidStrArray( array $uuid_arr ): bool
    {
        foreach ( $uuid_arr as $_uuid ) {
            if ( !Uuid::is_valid( $_uuid ) ) {
                return false;
            }
        }
        return !empty($uuid_arr);
    }

    /**
     * 檢查輸入SQL資料庫的時間字串
     *
     * @param string $datetimeString
     * @return bool
     */
    public static function is_sql_datetime_str( string $datetimeString ): bool
    {
        $dateTime = DateTime::createFromFormat(static::SQL_DATETIME_FORMAT, $datetimeString);
        if (!$dateTime) {
            return false; // 解析失敗，格式錯誤
        }
        // 額外檢查日期範圍必須有效
        $year = $dateTime->format('Y');
        $month = $dateTime->format('m');
        $day = $dateTime->format('d');
        // 限制年份範圍
        /*if ($year < 1970 || $year > 2100) {
            return false;
        }*/
        // 使用 checkdate() 驗證日期有效性
        if (!checkdate($month, $day, $year)) {
            return false;
        }
        // 如果通過所有檢查，則視為有效
        return $dateTime->format(static::SQL_DATETIME_FORMAT) === $datetimeString;
    }
}