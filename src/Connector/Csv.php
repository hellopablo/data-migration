<?php

namespace HelloPablo\DataMigration\Connector;

use HelloPablo\DataMigration\Interfaces\Connector;
use HelloPablo\DataMigration\Interfaces\Unit;

class Csv implements Connector
{
    /** @var Unit */
    protected $oUnit;

    /** @var string */
    protected $sFile;

    /** @var string[] */
    protected $aColumns;

    /** @var resource */
    protected $rFile;

    /** @var string */
    protected $sSeparator;

    /** @var string */
    protected $sEnclosure;

    /** @var string */
    protected $sEscape;

    // --------------------------------------------------------------------------

    /**
     * Connector constructor.
     *
     * @param Unit $oUnit The class to clone for each unit of work
     */
    public function __construct(
        Unit $oUnit,
        string $sFile = '',
        array $aColumns = [],
        string $sSeparator = ',',
        string $sEnclosure = '"',
        string $sEscape = '"'
    ) {
        $this->oUnit      = $oUnit;
        $this->sFile      = $sFile;
        $this->aColumns   = $aColumns;
        $this->sSeparator = $sSeparator;
        $this->sEnclosure = $sEnclosure;
        $this->sEscape    = $sEscape;
    }

    // --------------------------------------------------------------------------

    /**
     * Initiates the connection
     *
     * @return $this
     */
    public function connect(): self
    {
        if (empty($this->sFile)) {
            throw new \RuntimeException(
                'No CSV path provided',
            );
        }

        $this->rFile = fopen($this->sFile, 'r+');
        if ($this->rFile === false) {
            throw new \RuntimeException(sprintf(
                'Failed to open CSV for reading: %s',
                $this->sFile
            ));
        }
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Closes the connection
     *
     * @return $this
     */
    public function disconnect(): self
    {
        if ($this->rFile !== null) {
            fclose($this->rFile);
        }

        $this->rFile = null;

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
        // hat-tip: https://stackoverflow.com/a/59168627/789224
        $iCounter = 0;

        while (!feof($this->rFile)) {
            $oRow = $this->getRow();
            if (empty($oRow)) {
                continue;
            }
            $iCounter++;
        }

        rewind($this->rFile);

        return $iCounter;
    }

    // --------------------------------------------------------------------------

    /**
     * Reads records from the data source
     *
     * @return \Generator
     */
    public function read(): \Generator
    {
        while (!feof($this->rFile)) {
            $oRow = $this->getRow();
            if (!empty($oRow)) {
                yield (clone $this->oUnit)
                    ->setSource((object) $oRow)
                    ->setTarget((object) []);
            }
        }

        rewind($this->rFile);
    }

    // --------------------------------------------------------------------------

    /**
     * Returns a row from the CSV file
     *
     * @return \stdClass|null
     */
    protected function getRow(): ?\stdClass
    {
        $aRow = fgetcsv($this->rFile, 0, $this->sSeparator, $this->sEnclosure, $this->sEscape);
        if (empty($aRow)) {
            return null;
        }

        $oRow = new \stdClass();
        foreach ($aRow as $index => $value) {
            $oRow->{$this->aColumns[$index] ?? $index} = $value;
        }

        return $oRow;
    }

    // --------------------------------------------------------------------------

    /**
     * Writes a unit of work to the data source
     *
     * @param Unit $oUnit The unit to write
     *
     * @return $this
     */
    public function write(Unit $oUnit): self
    {
        fputcsv($this->rFile, $oUnit->toArray(), $this->sSeparator, $this->sEnclosure, $this->sEscape);
        return $this;
    }
}
