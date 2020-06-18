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
     * @return \Generator
     */
    public function yieldTransformers(): \Generator;
}
