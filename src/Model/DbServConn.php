<?php
declare(strict_types=1);

namespace App\Model;

use App\Obj\ProcResultObj;
use App\Model\SqlPdo;
use PDOException;

/**
 * SQL server connection and transaction functions for SQL data transact
 *
 * @author Nick
 * @since 2025-02-05
 */
class DbServConn extends SqlPdo
{
    /**
     * A pointer of PDO connection.
     *
     * @var object PDO
     */
    public $pdo = NULL;
    
    /**
     * Constructor and get pdo connection.
     *
     * @param array $db_settings
     */
    public function __construct ( array $db_settings )
    {
        $this->pdo =& parent::getInstance( $db_settings );
    }
    
    /**
     * 
     * @param array $rows
     * @param string $rowKey
     * @return array
     */
    public function getRowsColVal ( array $rows, string $rowKey ): array
    {
        $out = [];
        foreach ( $rows as $row ) {
            if ( isset( $row[ $rowKey ] ) ) {
                $out[] = $row[$rowKey];
            }
        }
        return $out;
    }

    /**
     *
     * @param string $sql
     * @param array|null $values
     * @param bool $isDebug
     * @return array|int
     */
    public function selectTransact ( string $sql, ?array $values = NULL, bool $isDebug = false ): array|int
    {
        $out = ProcResultObj::PROC_FAIL;
        if ( empty( $sql ) ) {
            return $out;
        }
        $stat = NULL;
        try {
            $stat = $this->pdo->prepare( $sql );
            if ( $stat->execute( $values ) ) {
                $out = $stat->fetchAll();
            }
            $stat->closeCursor();
        } catch ( PDOException $e ) {
            $out = self::sqlExceptionProc( $e, $isDebug );
        }
        if ( $stat !== NULL ) {
            $stat->closeCursor();
            $stat = NULL;
        }
        return $out;
    }

    /**
     * Including INSERT, UPDATE, REPLACE, DELETE
     *
     * @param string $sql
     * @param array|null $values
     * @param int|null $num output how many rows are effected.
     * @param bool $isDebug
     * @return int
     */
    public function writeTransact ( string $sql, ?array $values = NULL, ?int &$num = NULL, bool $isDebug = false ): int
    {
        $out = ProcResultObj::PROC_FAIL;
        if ( empty( $sql ) ) {
            return $out;
        }
        $stat = NULL;
        try {
            $this->pdo->beginTransaction();
            $stat = $this->pdo->prepare( $sql );
            if ( $stat->execute( $values ) ) {
                if ( !is_null( $num ) ) {
                    $num = $stat->rowCount();
                }
                $this->pdo->commit();
                $out = ProcResultObj::PROC_OK;
            } else {
                $this->pdo->rollBack();
            }
            $stat->closeCursor();
        } catch ( PDOException $e ) {
            $out = self::sqlExceptionProc( $e, $isDebug );
        }
        if ( $stat !== NULL ) {
            $stat->closeCursor();
            $stat = NULL;
        }
        return $out;
    }
    
    /**
     * IMPORTANT: If you call the function with pdo->beginTransaction(), pdo will roll back!
     *
     * @param mixed $e  a error reference
     * @param bool $isDebug
     * @return int Error code
     */
    public function sqlExceptionProc ( $e, bool $isDebug = false ): int
    {
        if ( $this->pdo->inTransaction() ) {
            $this->pdo->rollBack();
        }
        // output & stop for debug.
        if ( $isDebug ) {
            exit( $e->getMessage() );
        } else if (!( $e instanceof PDOException )) {   // 確保 errorInfo property 的存在，避免下面使用的時候出現系統錯誤
            return ProcResultObj::PROC_SERV_ERROR;
        } else if ( $e->errorInfo[0] == 23000 && $e->errorInfo[1] == 1062 ) {
            // if the name is duplicate under the company ID
            return ProcResultObj::PROC_DUPLICATE;
        } else if ( $e->errorInfo[0] == 23000 && ( $e->errorInfo[1] == 1451 || $e->errorInfo[1] == 1217 ) ) {
            // Cannot delete or update a parent row: a foreign key constraint fails
            // IMPORTANT mysql 8.x => return 1217, but mysql 5.7.x => return 1451
            return ProcResultObj::PROC_FOREIGN_KEY_CONSTRAINT;
        } else if ( $e->errorInfo[0] == 40001 && $e->errorInfo[1] == 1213 ) {
            // (ISO/ANSI) Serialization failure, e.g. timeout or deadlock.
            // NOTE: I hope that don't go here for error because it will make the auto increment add 1 counting.
            return ProcResultObj::PROC_SERIALIZATION_FAELURE;
        } else {
            return ProcResultObj::PROC_SERV_ERROR;
        }
    }
}
