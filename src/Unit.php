<?php

namespace HelloPablo\DataMigration;

use HelloPablo\DataMigration\Interfaces\Recipe;
use HelloPablo\DataMigration\Interfaces\Transformer;

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
     * @param Recipe $oRecipe The recipe to apply
     *
     * @return $this
     */
    public function applyRecipe(Recipe $oRecipe): self
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
     * @param Transformer $oTransformer
     *
     * @return $this
     */
    public function applyTransformer(Transformer $oTransformer): self
    {
        $this->oTarget->{$oTransformer->getTargetProperty()} = $oTransformer->transform(
            $this->oSource->{$oTransformer->getSourceProperty()}
        );

        return $this;
    }
}
