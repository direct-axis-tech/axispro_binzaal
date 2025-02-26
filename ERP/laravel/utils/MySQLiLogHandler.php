<?php
declare(strict_types=1);

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * This class is a handler for Monolog, which can be used
 * to write records in a MySQL table
 *
 * Class MySQLHandler
 * @package wazaari\MysqlHandler
 */
class MySQLiLogHandler extends AbstractProcessingHandler
{
    /**
     * mysqli object of database connection
     *
     * @var mysqli
     */
    private $mysqli;

    /**
     * mysqli statement which hold the prepared statement
     * 
     * @var mysqli_stmt
     */
    private $statement;

    /**
     * Holds whether the statement have been initialized or not
     * 
     * @var bool
     */
    private $initialized = false;
    
    /**
     * Constructor of this class, sets the PDO and calls parent constructor
     *
     * @param mysqli $mysqli PDO Connector for the database
     * @param string $table Table in the database to store the logs in
     * @param array $additionalFields Additional Context Parameters to store in database
     * @param bool $initialize Defines whether attempts to alter database should be skipped
     * @param bool|int $level Debug level which this handler should store
     * @param bool $bubble
     */
    public function __construct(
        mysqli $mysqli,
        int $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);

        $this->mysqli = $mysqli;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record): void
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $jsonContext = empty($record['context'])
            ? null
            : json_encode($record['context'], JSON_FORCE_OBJECT);
        $jsonExtra = empty($record['extra'])
            ? null
            : json_encode($record['extra'], JSON_FORCE_OBJECT);
        $dateTimeFormatted = $record['datetime']->format(DB_DATETIME_FORMAT);

        $this->statement->bind_param(
            'sissss',
            $record['channel'],
            $record['level'],
            $record['message'],
            $dateTimeFormatted,
            $jsonContext,
            $jsonExtra
        );

        $this->statement->execute();
    }

    /**
     * Initialize this log handler
     */
    protected function initialize(): void {
        $user = user_id();
        $ipv4 = getCurrentClientIP();
        $session_id = session_id();

        $this->statement = $this->mysqli->prepare(
            "INSERT INTO `0_logs` (
                `channel`,
                `level`,
                `message`,
                `timestamp`,
                `ip`,
                `user`,
                `session`,
                `context`,
                `extra`
            ) VALUES
                (?,?,?,?,'{$ipv4}','{$user}','{$session_id}',?,?)"
        );

        $this->initialized = true;
    }
}