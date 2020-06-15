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
}
