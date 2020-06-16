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
     * Returns an array of steps to apply to the unit
     *
     * @return Transformer[]
     */
    public function getTransformers(): array;
}
