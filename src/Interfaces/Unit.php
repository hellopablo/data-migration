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
     */
    public function __construct();

    // --------------------------------------------------------------------------

    /**
     * Sets the source object
     *
     * @param \stdClass $oSource The source object
     *
     * @return $this
     */
    public function setSource(\stdClass $oSource): self;

    // --------------------------------------------------------------------------

    /**
     * Sets the target object
     *
     * @param \stdClass $oTarget The target object
     *
     * @return $this
     */
    public function setTarget(\stdClass $oTarget): self;

    // --------------------------------------------------------------------------

    /**
     * Returns the source item's ID
     *
     * @return mixed
     */
    public function getSourceId();

    // --------------------------------------------------------------------------

    /**
     * Sets the target ID
     *
     * @param mixed $mId The ID of the target
     *
     * @return mixed
     */
    public function setTargetId($mId): self;

    // --------------------------------------------------------------------------

    /**
     * Returns the target item's ID
     *
     * @return mixed
     */
    public function getTargetId();

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
