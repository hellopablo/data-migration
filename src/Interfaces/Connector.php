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
     * Connector constructor.
     *
     * @param Unit $oUnit The class to clone for each unit of work
     */
    public function __construct(Unit $oUnit);

    // --------------------------------------------------------------------------

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
    public function read(): \Generator;

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
