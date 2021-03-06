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
     * Returns the source object
     *
     * @return \stdClass
     */
    public function getSource(): \stdClass;

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
     * Returns the target object
     *
     * @return \stdClass
     */
    public function getTarget(): \stdClass;

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
     * Tests whether the unit should be migrated
     */
    public function shouldMigrate(): void;

    // --------------------------------------------------------------------------

    /**
     * Determines if an item is migrated; returns the item's ID if so, null if not
     *
     * @param Pipeline $oPipeline The pipeline being migrated
     */
    public function isMigrated(Pipeline $oPipeline);

    // --------------------------------------------------------------------------

    /**
     * Applies a recipe to the unit
     *
     * @param Recipe   $oRecipe   The recipe to apply
     * @param Pipeline $oPipeline The pipeline being executed
     *
     * @return $this
     */
    public function applyRecipe(Recipe $oRecipe, Pipeline $oPipeline): self;

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
