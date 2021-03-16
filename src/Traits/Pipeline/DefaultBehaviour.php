<?php

namespace HelloPablo\DataMigration\Traits\Pipeline;

use HelloPablo\DataMigration\Exception\PipelineException\CommitException\SkipException;
use HelloPablo\DataMigration\Interfaces\Connector;
use HelloPablo\DataMigration\Interfaces\Recipe;
use HelloPablo\DataMigration\Interfaces\Unit;
use HelloPablo\DataMigration\Manager;

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
     *
     * @param Manager $oManager The Manager instance
     */
    public function commitStart(Manager $oManager): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Called before each unit of work
     *
     * @param Unit    $oUnit    The current unit of work
     * @param Manager $oManager The Manager instance
     */
    public function commitBefore(Unit $oUnit, Manager $oManager): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Called after each unit of work
     *
     * @param Unit    $oUnit    The current unit of work
     * @param Manager $oManager The Manager instance
     */
    public function commitAfter(Unit $oUnit, Manager $oManager): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Called if a unit of work is skipped
     *
     * @param Unit          $oUnit    The current unit of work
     * @param SkipException $e        The exception which was thrown
     * @param Manager       $oManager The Manager instance
     */
    public function commitSkipped(Unit $oUnit, SkipException $e, Manager $oManager): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Called if a unit of work errors
     *
     * @param Unit       $oUnit    The current unit of work
     * @param \Exception $e        The exception which was thrown
     * @param Manager    $oManager The Manager instance
     */
    public function commitError(Unit $oUnit, \Exception $e, Manager $oManager): void
    {
    }

    // --------------------------------------------------------------------------

    /**
     * Called at the end of the pipelie
     *
     * @param array   $aErrors  Any errors which occurred during commit
     * @param Manager $oManager The Manager instance
     */
    public function commitFinish(array $aErrors, Manager $oManager): void
    {
    }
}
