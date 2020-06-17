<?php

namespace HelloPablo\DataMigration\Interfaces;

/**
 * Interface Unit
 *
 * @package HelloPablo\DataMigration\Interfaces
 */
interface Unit
{
    /**
     * Unit constructor.
     *
     * @param \stdClass $oSource The source data
     */
    public function __construct(\stdClass $oSource);

    // --------------------------------------------------------------------------

    /**
     * Applies a recipe to the unit
     *
     * @param Recipe $oRecipe The recipe to apply
     *
     * @return $this
     */
    public function applyRecipe(Recipe $oRecipe): self;

    // --------------------------------------------------------------------------

    /**
     * Applies a transformer to a source property, saving to a target property
     *
     * @param Transformer $oTransformer The transformer to apply
     *
     * @return $this
     */
    public function applyTransformer(Transformer $oTransformer): self;

    // --------------------------------------------------------------------------

    /**
     * Returns the target object as an array
     *
     * @return array
     */
    public function toArray(): array;
}
