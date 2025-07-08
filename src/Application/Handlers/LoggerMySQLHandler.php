<?php
/**
 * Idea from wazaari\monolog-mysql.
 * 
 * This class is a handler for Monolog, which can be used to write records in a MySQL table
 * I remodified this code to fit my needs.
 * 
 * @author Nick Feng
 * @since 1.0
 */
namespace App\Application\Handlers;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use PDO;
use PDOStatement;

class LoggerMySQLHandler extends AbstractProcessingHandler
{
    /**
     * @var bool defines whether the MySQL connection is been initialized
     */
    private $initialized = false;

    /**
     * @var PDO pdo object of database connection
     */
    protected $pdo;

    /**
     * @var PDOStatement statement to insert a new record
     */
    private $statement;

    /**
     * @var string the table to store the logs in
     */
    private $table = 'logs';
    
    /**
     * NOTE: ***** PS. This is rebuild by Nick Feng at Jan.30th in 2018. *****
     *
     * id, channel, level, message, time, url, ip, method, server, referrer, user_agent
     * @var array default fields that are stored in db
     */
    private $defaultfields = array(
        'id', 
        'channel',
        'level', 
        'message', 
        'time', 
        'url', 
        'ip', 
        'method', 
        'server', 
        'referrer', 
        'user_agent'
    );
    
    /**
     * @var string[] additional fields to be stored in the database
     *
     * For each field $field, an additional context field with the name $field
     * is expected along the message, and further the database needs to have these fields
     * as the values are stored in the column name $field.
     */
    private $additionalFields = array();

    /**
     * @var array
     */
    private $fields = array();

    /**
     * @var bool defines whether database errors should be skipped
     */
    private $skipDBerror = true;

    /**
     * Constructor of this class, sets the PDO and calls parent constructor
     *
     * @param PDO $pdo PDO Connector for the database
     * @param string $table Table in the database to store the logs in
     * @param array $additionalFields Additional Context Parameters to store in database
     * @param int $level Debug level which this handler should store
     * @param bool $bubble
     * @param bool $skipDatabaseModifications Defines whether attempts to alter database should be skipped
     * @param bool $skipDBerror Defines whether database errors should be skipped
     */
    public function __construct(
        PDO $pdo,
        string $table,
        array $additionalFields = array(),
        int $level = Logger::DEBUG,
        bool $bubble = true,
        bool $skipDatabaseModifications = false,
        bool $skipDBerror = true
    ) {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->additionalFields = $additionalFields;
        $this->skipDBerror = $skipDBerror;
        parent::__construct($level, $bubble);

        if ($skipDatabaseModifications) {
            $this->mergeDefaultAndAdditionalFields();
            $this->initialized = true;
        }
    }

    /**
     * Initializes this handler by creating the table if it not exists
     */
    private function initialize()
    {
        // id, channel, level, message, time, url, ip, method, server, referrer
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS `'.$this->table.'` '
            .'(id CHAR(32) NOT NULL PRIMARY KEY, '
            .'channel VARCHAR(65), '
            .'level INTEGER, '
            .'message LONGTEXT, '
            .'time TIMESTAMP DEFAULT CURRENT_TIMESTAMP, '
            .'url TEXT, '
            .'ip TEXT, '
            .'method TEXT, '
            .'server TEXT, '
            .'referrer TEXT, '
            .'user_agent TEXT, '
            .'INDEX(channel) USING HASH, '
            .'INDEX(level) USING HASH, '
            .'INDEX(time) USING BTREE)'
        );

        //Read out actual columns
        $actualFields = array();
        $rs = $this->pdo->query('SELECT * FROM `' . $this->table . '` LIMIT 0');
        for ($i = 0; $i < $rs->columnCount(); $i++) {
            $col = $rs->getColumnMeta($i);
            $actualFields[] = $col['name'];
        }

        //Calculate changed entries
        $removedColumns = array_diff(
            $actualFields,
            $this->additionalFields,
            $this->defaultfields
        );
        $addedColumns = array_diff($this->additionalFields, $actualFields);

        //Remove columns
        if (!empty($removedColumns)) {
            foreach ($removedColumns as $c) {
                $this->pdo->exec('ALTER TABLE `' . $this->table . '` DROP `' . $c . '`;');
            }
        }

        //Add columns
        if (!empty($addedColumns)) {
            foreach ($addedColumns as $c) {
                $this->pdo->exec('ALTER TABLE `' . $this->table . '` add `' . $c . '` TEXT NULL DEFAULT NULL;');
            }
        }

        $this->mergeDefaultAndAdditionalFields();
        $this->initialized = true;
    }

    /**
     * Prepare the sql statment depending on the fields that should be written to the database
     */
    private function prepareStatement()
    {
        $columns = "";
        $fields = "";
        foreach ($this->fields as $key => $f) {
            if ($key == 0) {
                $columns .= "$f";
                $fields .= ":$f";
                continue;
            }
            $columns .= ", $f";
            $fields .= ", :$f";
        }
        $this->statement = $this->pdo->prepare(
            'INSERT INTO `' . $this->table . '` (' . $columns . ') VALUES (' . $fields . ')'
        );  
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param array $record []
     * @return void
     */
    protected function write(array $record): void
    {
        try {
            if (!$this->initialized) {
                $this->initialize();
            }
            
            $this->fields = $this->defaultfields;
            
            if (isset($record['extra'])) {
                $record['context'] = array_merge($record['context'], $record['extra']);
            }
            
            // id, channel, level, message, time, url, ip, method, server, referrer, user_agent
            //'context' contains the array
            $tz = new \DateTimeZone('UTC');
            $contentArray = array_merge(array(
                'id'      => md5($record['message'] . $record['datetime']->format('Y-m-d H:i:s') . uniqid('', true)), // substr(bin2hex(random_bytes((int) ceil(16 / 2))), 0, 16);
                'channel' => $record['channel'],
                'level'   => $record['level'],
                'message' => $record['message'],
                'time'    => $record['datetime']->format('Y-m-d H:i:s'),
                'url'     => (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null),
                'ip'      => (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null),
                'method'  => (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null),
                'server'  => (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null),
                'referrer'=> (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null),
                'user_agent' => (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null)
            ), $record['context']);
            
            // unset array keys that are passed put not defined to be stored, to prevent sql errors
            foreach ($contentArray as $key => $context) {
                if (!in_array($key, $this->fields)) {
                    unset($contentArray[$key]);
                    unset($this->fields[array_search($key, $this->fields)]);
                    continue;
                }
                
                if ($context === null) {
                    unset($contentArray[$key]);
                    unset($this->fields[array_search($key, $this->fields)]);
                }
            }

            $this->prepareStatement();

            //Remove unused keys
            foreach ($this->additionalFields as $key => $context) {
                if (!isset($contentArray[$key])) {
                    unset($this->additionalFields[$key]);
                }
            }
            
            //Fill content array with "null" values if not provided
            $contentArray = $contentArray + array_combine(
                $this->additionalFields,
                array_fill(0, count($this->additionalFields), null)
            );
            
            $this->statement->execute($contentArray);
        } catch (\Exception $e) {
            if (!$this->skipDBerror) {
                throw $e;
            }
            // If an error occurs, we just ignore it
            // This is done to prevent the application from crashing
            // and to ensure that the log handler does not interfere with the application flow
            // You can also log this error to a different log file if needed
        }
    }

    /**
     * Merges default and additional fields into one array
     */
    private function mergeDefaultAndAdditionalFields()
    {
        $this->defaultfields = array_merge($this->defaultfields, $this->additionalFields);
    }
}
