<?php

namespace HelloPablo\DataMigration\Transformer;

use HelloPablo\DataMigration\Interfaces;

/**
 * Class Set
 *
 * @package HelloPablo\DataMigration\Transformer
 */
class Set extends Copy
{
    /** @var mixed */
    protected $mValue;

    // --------------------------------------------------------------------------

    /**
     * Set constructor.
     *
     * @param string|null $sSourceProperty
     * @param string|null $sTargetProperty
     * @param mixed       $mValue
     */
    public function __construct(
        string $sSourceProperty = null,
        string $sTargetProperty = null,
        $mValue = null
    ) {
        parent::__construct($sSourceProperty, $sTargetProperty);
        $this->mValue = $mValue;
    }

    // --------------------------------------------------------------------------

    /**
     * Applies the transformation
     *
     * @param mixed           $mInput The value to transform
     * @param Interfaces\Unit $oUnit  The Unit being transformed
     */
    public function transform($mInput, Interfaces\Unit $oUnit)
    {
        return $this->mValue;
    }
}
