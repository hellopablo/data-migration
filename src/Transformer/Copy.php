<?php

namespace HelloPablo\DataMigration\Transformer;

use HelloPablo\DataMigration\Interfaces\Transformer;

/**
 * Class Copy
 *
 * @package HelloPablo\DataMigration\Transformer
 */
class Copy implements Transformer
{
    protected $sSourceProperty;
    protected $sTargetProperty;

    // --------------------------------------------------------------------------
    public function __construct(string $sSourceProperty, string $sTargetProperty)
    {
        $this->sSourceProperty = $sSourceProperty;
        $this->sTargetProperty = $sTargetProperty;
    }

    // --------------------------------------------------------------------------

    /**
     * @return string
     */
    public function getSourceProperty(): string
    {
        return $this->sSourceProperty;
    }

    // --------------------------------------------------------------------------

    /**
     * @return string
     */
    public function getTargetProperty(): string
    {
        return $this->sTargetProperty;
    }

    // --------------------------------------------------------------------------

    /**
     * Applies the transformation
     *
     * @param mixed $mInput The value to transform
     */
    public function transform($mInput)
    {
        return $mInput;
    }
}
