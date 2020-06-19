<?php

namespace HelloPablo\Exception;

use HelloPablo\DataMigration\Interfaces\Pipeline;
use HelloPablo\DataMigration\Interfaces\Unit;

/**
 * Class PipelineException
 *
 * @package HelloPablo\Exception
 */
class PipelineException extends DataMigrationException
{
    /** @var Pipeline */
    protected $oPipeline;

    /** @var Unit */
    protected $oUnit;

    // --------------------------------------------------------------------------

    /**
     * Set the Pipeline
     *
     * @param Pipeline $oPipeline The Pipeline to set
     */
    public function setPipeline(Pipeline $oPipeline): self
    {
        $this->oPipeline = $oPipeline;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Get the Pipeline
     *
     * @return Pipeline
     */
    public function getPipeline(): Pipeline
    {
        return $this->oPipeline;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the Unit
     *
     * @param Unit $oUnit The Unit to set
     */
    public function setUnit(Unit $oUnit): self
    {
        $this->oUnit = $oUnit;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Get the Unit
     *
     * @return Unit
     */
    public function getUnit(): Unit
    {
        return $this->oUnit;
    }
}
