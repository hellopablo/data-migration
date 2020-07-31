<?php

namespace HelloPablo\DataMigration\Interfaces;

use HelloPablo\DataMigration\Exception\PipelineException\CommitException\SkipException;

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

    // --------------------------------------------------------------------------

    /**
     * Called at the start of the pipelie
     */
    public function commitStart(): void;

    // --------------------------------------------------------------------------

    /**
     * Called before each unit of work
     *
     * @param Unit $oUnit The current unit of work
     */
    public function commitBefore(Unit $oUnit): void;

    // --------------------------------------------------------------------------

    /**
     * Called after each unit of work
     *
     * @param Unit $oUnit The current unit of work
     */
    public function commitAfter(Unit $oUnit): void;

    // --------------------------------------------------------------------------

    /**
     * Called if a unit of work is skipped
     *
     * @param Unit          $oUnit The current unit of work
     * @param SkipException $e     The exception which was thrown
     */
    public function commitSkipped(Unit $oUnit, SkipException $e): void;

    // --------------------------------------------------------------------------

    /**
     * Called if a unit of work errors
     *
     * @param Unit       $oUnit The current unit of work
     * @param \Exception $e     The exception which was thrown
     */
    public function commitError(Unit $oUnit, \Exception $e): void;

    // --------------------------------------------------------------------------

    /**
     * Called at the end of the pipelie
     *
     * @param array $aErrors Any errors which occurred during commit
     */
    public function commitFinish(array $aErrors): void;
}
