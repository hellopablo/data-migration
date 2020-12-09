<?php

namespace HelloPablo\DataMigration\Interfaces;

/**
 * Interface Recipe
 *
 * @package HelloPablo\DataMigration\Interfaces
 */
interface Recipe
{
    /**
     * Yields transformers to apply to the unit
     *
     * @param Pipeline $oPipeline The Pipeline being executed
     *
     * @return \Generator
     */
    public function yieldTransformers(Pipeline $oPipeline): \Generator;
}
