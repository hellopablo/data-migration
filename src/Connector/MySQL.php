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
     * @param string|null $sHost     The host to connect to
     * @param string|null $sUsername The username to connect with
     * @param string|null $sPassword The password to connect with
     * @param int|null    $iPort     The port number to connect on
     * @param string|null $sDatabase The database to use
     * @param string|null $sTable    The table to use
     */
    public function __construct(
        string $sHost = null,
        string $sUsername = null,
        string $sPassword = null,
        int $iPort = null,
        string $sDatabase = null,
        string $sTable = null
    ) {
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
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8',
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
     * Reads records from the data source
     *
     * @return \Generator
     */
    public function read(): \Generator
    {
        $oStatement = $this->oPdo->query('SELECT * FROM `' . $this->sTable . '`');

        while ($oRow = $oStatement->fetch(\PDO::FETCH_OBJ)) {
            yield new \HelloPablo\DataMigration\Unit($oRow);
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
        //  @todo (Pablo - 2020-06-16) - Implement method
        dd('DATA WRITE');
        return $this;
    }
}
