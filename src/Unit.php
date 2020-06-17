<?php

namespace HelloPablo\DataMigration;

use HelloPablo\DataMigration\Interfaces;

/**
 * Class Unit
 *
 * @package HelloPablo\DataMigration
 */
class Unit implements \HelloPablo\DataMigration\Interfaces\Unit
{
    /** @var \stdClass */
    protected $oSource;

    /** @var \stdClass */
    protected $oTarget;

    // --------------------------------------------------------------------------

    /**
     * Unit constructor.
     *
     * @param \stdClass $oSource The source data
     */
    public function __construct(\stdClass $oSource)
    {
        $this->oSource = $oSource;
    }

    // --------------------------------------------------------------------------

    /**
     * Applies a recipe to the unit
     *
     * @param Interfaces\Recipe $oRecipe The recipe to apply
     *
     * @return $this
     */
    public function applyRecipe(Interfaces\Recipe $oRecipe): Interfaces\Unit
    {
        $aTransformers = $oRecipe->getTransformers();
        $this->oTarget = (object) [];

        foreach ($aTransformers as $oTransformer) {
            $this->applyTransformer($oTransformer);
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Applies a transformer to a source property, saving to a target property
     *
     * @param Interfaces\Transformer $oTransformer The transformer to apply
     *
     * @return $this
     */
    public function applyTransformer(Interfaces\Transformer $oTransformer): Interfaces\Unit
    {
        $sSourceProperty = $oTransformer->getSourceProperty();
        $sTargetProperty = $oTransformer->getTargetProperty();

        if ($sSourceProperty === null) {
            $this->oTarget->{$sTargetProperty} = $oTransformer->transform(null);

        } elseif (!property_exists($this->oSource, $sSourceProperty)) {
            throw new \RuntimeException(
                sprintf(
                    'Property %s does not exist on source object',
                    $sSourceProperty
                )
            );

        } elseif (empty($sTargetProperty)) {
            throw new \RuntimeException(
                sprintf(
                    'Target property must be specified',
                    $sSourceProperty
                )
            );

        } else {
            $this->oTarget->{$sTargetProperty} = $oTransformer->transform(
                $this->oSource->{$sSourceProperty}
            );
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the target object as an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return (array) $this->oTarget;
    }
}
