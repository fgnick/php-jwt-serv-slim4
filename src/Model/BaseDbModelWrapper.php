<?php
declare(strict_types=1);

namespace App\Model;

use App\Obj\ProcResultObj;
use App\Obj\BaseProcResult;

use ReflectionClass;
use RuntimeException;
use Throwable;

/**
 * This class serves as a base wrapper for database model operations.
 * It provides a unified entry point for all methods, ensuring that
 * the return values are consistently wrapped in a ProcResultObj.
 * 
 * @author Nick Feng
 * @version 1.0
 */
abstract class BaseDbModelWrapper extends BaseProcResult
{
    /**
     * use magic method to 統一入口，包裝所有回傳值
     * 如果你要將所有與這個class有關的method都包裝成ProcResultObj，
     * 要記得繼承他的child的method必須要是非public的function
     */
    public function __call(string $method, array $args): ProcResultObj
    {
        try {
            $result = $this->$method(...$args);
            // 如果已經是 ProcResultObj 就直接回傳
            if ($result instanceof ProcResultObj) {
                return $result;
            } elseif (is_array($result) || is_object($result)) {
                // 如果是陣列或物件，則包裝成 ProcResultObj
                return new ProcResultObj(
                    true, 
                    static::PROC_OK, 
                    $result, 
                    ProcResultObj::getCodeText(static::PROC_OK)
                );
            } elseif ( is_int( $result ) ) {
                // 如果是整數，則視為code from ProcResultInterface，包裝成 ProcResultObj
                $constants = (new ReflectionClass(static::class))->getConstants();
                if (in_array($result, $constants, true)) {
                    $is_success = true; // 如果是 PROC_OK，則視為成功
                    if ($result !== static::PROC_OK) {
                        // 如果不是 PROC_OK，則視為失敗
                        $is_success = false;
                    }
                    return new ProcResultObj($is_success, $result, [], ProcResultObj::getCodeText($result));
                } else {
                    // 不是合法常數，丟出例外
                    throw new RuntimeException('export data error: int must be a ProcResultInterface constant');
                }
            } else {
                // 如果是其他類型，則視為錯誤。提醒開發者要遵守。
                throw new RuntimeException('export data error: return value must be array, object, or ProcResultObj');
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'export data error: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
            //return new ProcResultObj(false, static::PROC_SERV_ERROR, [], $e->getMessage());
        }
    }
}