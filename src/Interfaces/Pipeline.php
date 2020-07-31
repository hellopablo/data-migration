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
     * Returns the priority of the pipeline, lower priority will run first
     *
     * @return int
     */
    public static function getPriority(): int;

    // --------------------------------------------------------------------------

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

    // --------------------------------------------------------------------------

    /**
     * Returns the recipe for the pipeline
     *
     * @return Recipe
     */
    public function getRecipe(): Recipe;
}
