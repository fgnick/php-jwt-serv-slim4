<?php
namespace App\Obj;

interface ObjInterface
{
    // IMPORTANT: property name 請與 table 的 column name 一樣，這樣比較好在 SQL 的時候轉換方便性較佳
    public function __construct();

    /**
     * 方便從資料庫輸出的row，可以快速的將資料放到此物件之中的所有對應 properties。但是，物件的properties必須名稱也要與資料庫表單欄位相同。
     *
     * @param array $row
     * @return void
     */
    public function importSqlRow( array $row ): void;

    /**
     * To detect the object is empty or not
     * 
     * @return boolean
     */
    public function isEmpty(): bool;

    /**
     * Don't suggest to use the method Individually
     * 如果任何一個欄位的數值是 NULL，則代表這個欄位的數值是可以忽視的。
     * 但是，如果是空字串的話就不可以忽視。因為那有可能是它想要清除字串，才用空字串來消除
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function isValid( string $key, $value = NULL ): bool;

    /**
     * @param string $key
     * @return mixed
     */
    public function __get( string $key );

    /**
     * @param string $key
     * @param $value
     * @return void
     */
    public function __set(string $key, $value );

    /**
     *
     * @param string $key
     * @return boolean
     */
    public function __isset( string $key );

    /**
     *
     * @param string $key
     */
    public function __unset( string $key );
}