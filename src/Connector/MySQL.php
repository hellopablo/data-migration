<?php

namespace HelloPablo\DataMigration\Connector;

use HelloPablo\DataMigration\Interfaces\Connector;
use HelloPablo\DataMigration\Interfaces\Unit;

/**
 * Class MySQL
 *
 * @package HelloPablo\DataMigration\Connector
 */
class MySQL implements Connector
{
    /**
     * The string to use for the DSN, will be passed:
     * - Host
     * - Port
     * - Database
     *
     * @var string
     */
    const DSN_STIRNG = 'mysql:host=%s;port=%s;dbname=%s;charset=utf8';

    /**
     * The query used for reading items, will be passed:
     * - Table
     *
     * @var string
     */
    const READ_QUERY = 'SELECT * FROM `%s`;';

    /**
     * The query used for writing items, will be passed:
     * - Table
     * - Columns
     * - Escaped values
     *
     * @var string
     */
    const WRITE_QUERY = 'INSERT INTO `%s` (%s) VALUES (%s)';

    // --------------------------------------------------------------------------

    /** @var Unit */
    protected $oUnit;

    /** @var string|null */
    protected $sHost;

    /** @var string|null */
    protected $sUsername;

    /** @var string|null */
    protected $sPassword;

    /** @var int|null */
    protected $iPort;

    /** @var string|null */
    protected $sDatabase;

    /** @var string|null */
    protected $sTable;

    /** @var \PDO */
    protected $oPdo;

    // --------------------------------------------------------------------------

    /**
     * MySQL constructor.
     *
     * @param Unit        $oUnit     The class to use for each unit of work
     * @param string|null $sHost     The host to connect to
     * @param string|null $sUsername The username to connect with
     * @param string|null $sPassword The password to connect with
     * @param int|null    $iPort     The port number to connect on
     * @param string|null $sDatabase The database to use
     * @param string|null $sTable    The table to use
     */
    public function __construct(
        Unit $oUnit,
        string $sHost = null,
        string $sUsername = null,
        string $sPassword = null,
        int $iPort = null,
        string $sDatabase = null,
        string $sTable = null
    ) {
        $this->oUnit     = $oUnit;
        $this->sHost     = $sHost ?? '127.0.0.1';
        $this->sUsername = $sUsername ?? '';
        $this->sPassword = $sPassword ?? '';
        $this->iPort     = $iPort ?? 3306;
        $this->sDatabase = $sDatabase ?? '';
        $this->sTable    = $sTable ?? '';
    }

    // --------------------------------------------------------------------------

    /**
     * Opens the MySQL connection
     *
     * @return $this
     */
    public function connect(): Connector
    {
        $this->oPdo = new \PDO(
            sprintf(
                static::DSN_STIRNG,
                $this->sHost,
                $this->iPort,
                $this->sDatabase
            ),
            $this->sUsername,
            $this->sPassword
        );

        $this->oPdo->exec('set names utf8');
        $this->oPdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Closes the MySQL connection
     *
     * @return $this
     */
    public function disconnect(): Connector
    {
        $this->oPdo = null;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Whether or not the connector can rollback changes if an error is encountered
     *
     * @return bool
     */
    public function supportsTransactions(): bool
    {
        return false;
    }

    // --------------------------------------------------------------------------

    /**
     * Counts the expected number of operations
     *
     * @return int
     */
    public function count(): int
    {
        $oStatement = $this->oPdo->query(
            sprintf(
                static::READ_QUERY,
                $this->sTable
            )
        );

        return $oStatement->rowCount();
    }

    // --------------------------------------------------------------------------

    /**
     * Reads records from the data source
     *
     * @return \Generator
     */
    public function read(): \Generator
    {
        $oStatement = $this->oPdo->query(
            sprintf(
                static::READ_QUERY,
                $this->sTable
            )
        );

        while ($oRow = $oStatement->fetch(\PDO::FETCH_OBJ)) {
            yield (clone $this->oUnit)
                ->setSource($oRow)
                ->setTarget((object) []);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Writes a unit of work to the data source
     *
     * @param Unit $oUnit The unit to write
     *
     * @return $this
     */
    public function write(Unit $oUnit): Connector
    {
        $aData = $oUnit->toArray();
        $aKeys = array_keys($aData);

        $oStatement = $this->oPdo->prepare(
            sprintf(
                static::WRITE_QUERY,
                $this->sTable,
                implode(', ', array_map(function (string $sProperty) {
                    return '`' . $sProperty . '`';
                }, $aKeys)),
                implode(', ', array_map(function (string $sProperty) {
                    return ':' . $sProperty;
                }, $aKeys))
            )
        );

        $oStatement->execute($aData);

        $oUnit->setTargetId($this->oPdo->lastInsertId());

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the PDO API
     *
     * @return \Pdo
     */
    public function getApi(): \Pdo
    {
        return $this->oPdo;
    }
}
