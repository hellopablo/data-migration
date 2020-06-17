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
    /** @var string|null */
    protected $sSourceProperty;

    /** @var string */
    protected $sTargetProperty;

    // --------------------------------------------------------------------------

    /**
     * Copy constructor.
     *
     * @param string|null $sSourceProperty The source property
     * @param string|null      $sTargetProperty The target property
     */
    public function __construct(string $sSourceProperty = null, string $sTargetProperty = null)
    {
        $this->sSourceProperty = $sSourceProperty;
        $this->sTargetProperty = $sTargetProperty;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the source property, if null then the field is considered new
     *
     * @return ?string
     */
    public function getSourceProperty(): ?string
    {
        return $this->sSourceProperty;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the target property
     *
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
