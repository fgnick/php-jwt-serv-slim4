<?php

declare(strict_types=1);

/**
 * 如果你有自己一套喜好的標準，這邊可ˇ以自己修改成自己喜歡的文字以及編號。
 * 
 * @author Nick Fegn
 * @since 1.0.0
 */
namespace App\Obj;

class BaseProcResult
{
    // status code for returning.
    public const PROC_FAIL           = 0x00;
    public const PROC_OK             = 0x01;   // 0x01 ~ 0x0F 可子針對 success 回應做自由發揮
    public const PROC_INVALID        = 0x10;
    public const PROC_NO_ACCESS      = 0x11;
    public const PROC_DATA_FULL      = 0x12;
    public const PROC_INVALID_USER   = 0x13;
    public const PROC_INVALID_PW     = 0x14;
    public const PROC_NOT_EXISTED    = 0x15;
    public const PROC_BLOCKED        = 0x16;
    public const PROC_UNINITIALIZED  = 0x17;   //uninitialized
    public const PROC_TOKEN_ERROR    = 0x18;
    public const PROC_MEM_VIEW_ERROR = 0x19;
    public const PROC_FILE_INVALID   = 0x1A;
    public const PROC_WAITING        = 0x1B;
    public const PROC_EXCEEDED_ATTEMPT = 0x1C;

    // for SQL error
    public const PROC_FOREIGN_KEY_CONSTRAINT = 0xFC;   // foreign key constraint fails
    public const PROC_SERIALIZATION_FAELURE  = 0xFD;   // deadlock table
    public const PROC_DUPLICATE              = 0xFE;
    public const PROC_SERV_ERROR             = 0xFF;
    
    /**
     * Convert processing code to text.
     * 
     * @var array
     */
    public const PROC_TXT = [
        self::PROC_FAIL          => 'fail',
        self::PROC_OK            => 'ok',   // 0x01 ~ 0x0F 可子針對 success 回應做自由發揮
        self::PROC_INVALID       => 'invalid input',
        self::PROC_NO_ACCESS     => 'access denied',
        self::PROC_DATA_FULL     => 'data full',
        self::PROC_INVALID_USER  => 'invalid ID',
        self::PROC_INVALID_PW    => 'invalid password',
        self::PROC_NOT_EXISTED   => 'not existed',
        self::PROC_BLOCKED       => 'blocked',
        self::PROC_UNINITIALIZED => 'uninitialized',
        self::PROC_TOKEN_ERROR   => 'token error',
        self::PROC_MEM_VIEW_ERROR=> 'user view error',
        self::PROC_FILE_INVALID  => 'file invalid',
        self::PROC_WAITING       => 'process waiting',
        self::PROC_EXCEEDED_ATTEMPT => 'You\'ve exceeded the maximum number of attempts',
        // for SQL error
        self::PROC_FOREIGN_KEY_CONSTRAINT => 'data with key constraint',   // foreign key constraint fails
        self::PROC_SERIALIZATION_FAELURE  => 'data serialization failure',   // deadlock table or timeout
        self::PROC_DUPLICATE              => 'data duplicate',
        self::PROC_SERV_ERROR             => 'server internal error'
    ];

    /**
     * Convert processing code to text.
     *
     * @param integer $code
     * @return string
     */
    public static function getCodeText(int $code): string
    {
        return static::PROC_TXT[$code] ?? 'Unknown code error!';
    }
}