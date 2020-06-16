<?php

namespace HelloPablo\DataMigration\Interfaces;

/**
 * Interface Pipeline
 *
 * @package HelloPablo\DataMigration\Interfaces
 */
interface Pipeline
{
    /**
     * Returns the connector to use for the data source
     *
     * @return Connector
     */
    public function getSourceConnector(): Connector;

    // --------------------------------------------------------------------------

    /**
     * Returns the connector to use for the data target
     *
     * @return Connector
     */
    public function getTargetConnector(): Connector;
}
