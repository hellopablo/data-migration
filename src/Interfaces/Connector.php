<?php

namespace HelloPablo\DataMigration\Interfaces;

/**
 * Interface Connector
 *
 * @package HelloPablo\DataMigration\Interfaces
 */
interface Connector
{
    /**
     * Initiates the connection
     *
     * @return $this
     */
    public function connect(): self;

    // --------------------------------------------------------------------------

    /**
     * Closes the connection
     *
     * @return $this
     */
    public function disconnect(): self;

    // --------------------------------------------------------------------------

    /**
     * Reads records from the data source
     *
     * @return \Generator
     */
    public function read($sUnitClass = \HelloPablo\DataMigration\Unit::class): \Generator;

    // --------------------------------------------------------------------------

    /**
     * Writes a unit of work to the data source
     *
     * @param Unit $oUnit The unit to write
     *
     * @return $this
     */
    public function write(Unit $oUnit): self;
}
