<?php

namespace HelloPablo\DataMigration\Interfaces;

/**
 * Interface Reader
 *
 * @package HelloPablo\DataMigration\Interfaces
 */
interface Reader
{
    /**
     * Reader constructor.
     *
     * @param Connector $oConnector The connector to use
     */
    public function __construct(Connector $oConnector);

    // --------------------------------------------------------------------------

    /**
     * Returns the total number of units which will be processed by the reader
     *
     * @return int
     */
    public function totalUnits(): int;

    // --------------------------------------------------------------------------

    /**
     * Returns the next unit of work
     *
     * @param Unit|null $oPreviousUnit The previously completed unit of work
     *
     * @return Unit|null
     */
    public function getUnit(?Unit $oPreviousUnit): ?Unit;
}
