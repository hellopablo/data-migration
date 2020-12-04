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
     * @param string|null $sSourceProperty
     * @param string      $sTargetProperty
     */
    public function __construct(?string $sSourceProperty, string $sTargetProperty);

    // --------------------------------------------------------------------------

    /**
     * Returns the source property, if null then the field is considered new
     *
     * @return ?string
     */
    public function getSourceProperty(): ?string;

    // --------------------------------------------------------------------------

    /**
     * Returns the target property
     *
     * @return string
     */
    public function getTargetProperty(): string;

    // --------------------------------------------------------------------------

    /**
     * Applies the transformation
     *
     * @param mixed $mInput The value to transform
     * @param Unit  $oUnit  The unit being being transformed
     */
    public function transform($mInput, Unit $oUnit);
}
