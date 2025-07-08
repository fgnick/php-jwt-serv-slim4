<?php
namespace App\Obj;

use Exception;
use IteratorAggregate;
use ArrayIterator;
use Traversable;

use App\Lib\ValueValidate;

/**
 * A typical JWT payload data saving object.
 * 
 * payload: {
 *     'iss'  => $this->iss,   // Issuer
 *     'exp'  => $this->exp,   //(new \DateTime('now +24 hours'))->getTimeStamp(),// Expire default is a week
 *     'sub'  => $this->sub,   // 如果一個用戶以 ID "user123" 登錄，那麼 JWT 中的 sub 聲明可能就是 "sub": "user123"。
 *     'aud'  => $this->aud,   // Audience: the recipient for whom the JWT is intended. It can be a single recipient or an array of recipients. like a channel name
 *     'nbf'  => $this->nbf,   // Not before. 也就是說雖然有發這個 JWT，但是在這個時間之前，這個token仍然是不可以使用的。
 *     'iat'  => $this->iat,   // Issued at: time when the token was generated
 *     'jti'  => $this->jti,   // Json Token Id: An unique identifier for the token
 *     'claims' => $this->claims // customized data. it is good to be an array or an object
 * }
 * 
 * @author Nick Feng
 * @since 1.0
 */
class JwtPayloadObj implements ObjInterface, IteratorAggregate
{
    // IMPORTANT: property name 請與 table 的 column name 一樣，這樣比較好在 SQL 的時候轉換方便性較佳
    public $iss;    // Issuer
    public $exp;    // token expired time
    public $sub;    // Subject: the subject of the JWT. This is usually a unique identifier for the user or entity that the JWT represents.
    public $aud;    // Audience: the recipient for whom the JWT is intended. It can be a single recipient or an array of recipients. like a channel name
    public $nbf;    // Not before
    public $iat;    // Issued at: time when the token was generated
    public $jti;    // Json Token Id: An unique identifier for the token
    public $claims;   // customized data. it is good to be an array or an object
    
    // for database, not in JWT payload
    public $mem_uid;
    public $create_on;

    /**
     * 這邊不做任何資料的自動生成，因為是物件，所以是用來儲存使用。
     * 如果要自動生成 payload 的隨機碼，請用 JwtPayload::genPayload()
     *
     * @param string|null $iss
     * @param integer|null $exp
     * @param string|null $sub
     * @param string|null $aud
     * @param integer|null $nbf
     * @param integer|null $iat
     * @param string|null $jti
     * @param array|null $claims
     * @param string|null $mem_uid
     * @param integer|null $create_on
     */
    public function __construct(
        ?string $iss = NULL,
        ?int $exp = NULL,
        ?string $sub = NULL,
        ?string $aud = NULL,
        ?int $nbf = NULL,
        ?int $iat = NULL,
        ?string $jti = NULL,
        ?array $claims = NULL,
        ?string $mem_uid = NULL,
        ?int $create_on = NULL
    ) {
        $this->iss = $iss;
        $this->exp = $exp;
        $this->sub = $sub;
        $this->aud = $aud;
        $this->nbf = $nbf;
        $this->iat = $iat;
        $this->jti = $jti;
        $this->claims = $claims;

        $this->mem_uid = $mem_uid;
        $this->create_on = $create_on;
    }
    
    /**
     * export a stander JWT payload array.
     *
     * @return array
     */
    public function toPayload(): array
    {
        return [
            'iss'  => $this->iss,   // Issuer
            'exp'  => $this->exp,   //(new \DateTime('now +24 hours'))->getTimeStamp(),// Expire default is a week
            'sub'  => $this->sub,   // 如果一個用戶以 ID "user123" 登錄，那麼 JWT 中的 sub 聲明可能就是 "sub": "user123"。
            'aud'  => $this->aud,   // Audience: the recipient for whom the JWT is intended. It can be a single recipient or an array of recipients. like a channel name
            'nbf'  => $this->nbf,   // Not before. 也就是說雖然有發這個 JWT，但是在這個時間之前，這個token仍然是不可以使用的。
            'iat'  => $this->iat,   // Issued at: time when the token was generated
            'jti'  => $this->jti,   // Json Token Id: An unique identifier for the token
            'claims' => $this->claims // customized data. it is good to be an array or an object
        ];
    }

    /**
     * 方便從資料庫輸出的 row，可以快速的將資料放到此物件之中的所有對應 properties。但是，物件的 properties 必須名稱也要與資料庫表單欄位相同。
     *
     * @param array $row
     * @return void
     */
    public function importSqlRow( array $row ): void
    {
        foreach ( $row as $key => $value ) {
            if ( property_exists( $this, $key ) ) { // 只要存在，就把SQL得到的值，賦予給該 property
                // 只要管理好 insert & update 資料的格式正確，其實這邊就不需要再檢查資料正確性了
                $this->$key = $value;
            }
        }
    }
    
    /**
     * To detect the object is empty or not
     * 
     * @return boolean
     */
    public function isEmpty(): bool
    {
        foreach ($this->getIterator() as $value) {
            if (!is_null($value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Allow foreach iteration over object properties.
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator(get_object_vars($this));
    }

    /**
     * Don't suggest to use the method Individually
     * 如果任何一個欄位的數值是 NULL，則代表這個欄位的數值是可以忽視的。
     * 但是，如果是空字串的話就不可以忽視。因為那有可能是它想要清除字串，才用空字串來消除
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function isValid( string $key, $value = NULL ): bool
    {
        if ( property_exists($this, $key) ) {
            $value = is_null($value) ? $this->$key : $value;
            switch ($key) {
                case 'jti': // it cannot be NULL
                    if (is_string($value) && ValueValidate::is_token_jti($value)) {
                        return true;
                    }
                    return false;
                case 'mem_uid':
                    if (is_string($value) && ValueValidate::is_user_uid($value)) {
                        return true;
                    }
                    return false;
                case 'nbf':
                case 'iat':
                case 'create_on':
                case 'exp':
                    if (is_int($value) && $value >= 0) {
                        return true;
                    }
                    return false;
                case 'sub':
                case 'aud':
                case 'iss':
                    if (is_string($value) && ValueValidate::is_app_issuer($value)) {
                        return true;
                    }
                    return false;
                case 'claims':
                    if (is_array($value) || is_object($value)) {
                        return true;
                    }
                    return false;
            }
        }
        return false;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get( string $key )
    {
        return $this->$key;
    }

    /**
     * @param string $key
     * @param $value
     * @return void
     * @throws Exception
     */
    public function __set(string $key, $value )
    {
        if ( !self::isValid( $key, $value )) {
            throw new Exception( $key . ' is assigned an invalid value' );
        }
        $this->$key = $value;
    }

    /**
     *
     * @param string $key
     * @return boolean
     */
    public function __isset( string $key )
    {
        return isset( $this->$key );
    }

    /**
     *
     * @param string $key
     */
    public function __unset( string $key )
    {
        unset( $this->$key );
    }
}