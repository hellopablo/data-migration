<?php

namespace HelloPablo\DataMigration\Interfaces;

/**
 * Interface Transformer
 *
 * @package HelloPablo\DataMigration\Interfaces
 */
interface Transformer
{
    /**
     * Transformer constructor.
     *
     * @param string $sSourceProperty
     * @param string $sTargetProperty
     */
    public function __construct(string $sSourceProperty, string $sTargetProperty);

    // --------------------------------------------------------------------------

    /**
     * Applies the transformation
     *
     * @param mixed $mInput The value to transform
     */
    public function transform($mInput);
}
