<?php

namespace HelloPablo\DataMigration\Transformer;

use HelloPablo\DataMigration\Interfaces;

/**
 * Class Group
 *
 * @package HelloPablo\DataMigration
 */
class Group extends Copy
{
    /** @var array string[] */
    protected $aTransformers = [];

    // --------------------------------------------------------------------------

    /**
     * Group constructor.
     *
     * @param string|null $sSourceProperty The source property
     * @param string|null $sTargetProperty The target property
     * @param string[]    $aTransformers   An array of transformation classes
     */
    public function __construct(string $sSourceProperty = null, string $sTargetProperty = null, array $aTransformers = [])
    {
        parent::__construct($sSourceProperty, $sTargetProperty);
        $this->$aTransformers = $aTransformers;
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
        foreach (static::$aTransformers as $sTransformer) {
            if ($sTransformer instanceof Interfaces\Transformer) {
                $mInput = $mTransformer->transform($mInput, $oUnit);
            } else {
                $mInput = call_user_func($mTransformer . '::transform', $mInput, $oUnit);
            }
        }

        return $mInput;
    }
}
