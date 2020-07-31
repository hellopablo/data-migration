<?php

namespace HelloPablo\DataMigration\Traits\Pipeline;

use HelloPablo\DataMigration\Exception\PipelineException\CommitException\SkipException;
use HelloPablo\DataMigration\Interfaces\Connector;
use HelloPablo\DataMigration\Interfaces\Recipe;
use HelloPablo\DataMigration\Interfaces\Unit;

trait DefaultBehaviour
{
    /**
     * Returns the priority of the pipeline, lower priority will run first
     *
     * @return int
     */
    public static function getPriority(): int
    {
        return 0;
    }

    // --------------------------------------------------------------------------

    /**
     * Called at the start of the pipelie
     */
    public function commitStart(): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Called before each unit of work
     *
     * @param Unit $oUnit The current unit of work
     */
    public function commitBefore(Unit $oUnit): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Called after each unit of work
     *
     * @param Unit $oUnit The current unit of work
     */
    public function commitAfter(Unit $oUnit): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Called if a unit of work is skipped
     *
     * @param Unit          $oUnit The current unit of work
     * @param SkipException $e     The exception which was thrown
     */
    public function commitSkipped(Unit $oUnit, SkipException $e): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Called if a unit of work errors
     *
     * @param Unit       $oUnit The current unit of work
     * @param \Exception $e     The exception which was thrown
     */
    public function commitError(Unit $oUnit, \Exception $e): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Called at the end of the pipelie
     *
     * @param array $aErrors Any errors which occurred during commit
     */
    public function commitFinish(array $aErrors): void
    {
    }
}
