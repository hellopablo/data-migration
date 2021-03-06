<?php

namespace HelloPablo\DataMigration;

use HelloPablo\DataMigration\IdMapper\Callback;
use HelloPablo\DataMigration\Interfaces;
use HelloPablo\DataMigration\Interfaces\Pipeline;

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
     * @param \stdClass|null $oSource The source data
     */
    public function __construct(\stdClass $oSource = null)
    {
        $this->oSource = $oSource;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the source object
     *
     * @param \stdClass $oSource The source object
     *
     * @return $this
     */
    public function setSource(\stdClass $oSource): Interfaces\Unit
    {
        $this->oSource = $oSource;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the source object
     *
     * @return \stdClass
     */
    public function getSource(): \stdClass
    {
        return $this->oSource;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the target object
     *
     * @param \stdClass $oTarget The target object
     *
     * @return $this
     */
    public function setTarget(\stdClass $oTarget): Interfaces\Unit
    {
        $this->oTarget = $oTarget;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the target object
     *
     * @return \stdClass
     */
    public function getTarget(): \stdClass
    {
        return $this->oTarget;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the source item's ID
     *
     * @return mixed
     */
    public function getSourceId()
    {
        return $this->getSource()->id ?? null;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the target ID
     *
     * @param mixed $mId the target ID
     *
     * @return $this
     */
    public function setTargetId($mId): Interfaces\Unit
    {
        $this->getTarget()->id = $mId;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the target item's ID
     *
     * @return mixed
     */
    public function getTargetId()
    {
        return $this->getTarget()->id ?? null;
    }

    // --------------------------------------------------------------------------

    /**
     * Tests whether the unit should be migrated
     */
    public function shouldMigrate(): void
    {
        /**
         * Throw an exception if the unit should _not_ be migrated. The exception
         * message will be logged
         */
    }

    // --------------------------------------------------------------------------

    /**
     * Determines if an item is migrated; returns the item's ID if so, null if not
     *
     * @param Pipeline $oPipeline The pipeline being migrated
     */
    public function isMigrated(Interfaces\Pipeline $oPipeline)
    {
        return null;
    }

    // --------------------------------------------------------------------------

    /**
     * Applies a recipe to the unit
     *
     * @param Interfaces\Recipe $oRecipe The recipe to apply
     *
     * @return $this
     */
    public function applyRecipe(Interfaces\Recipe $oRecipe, Interfaces\Pipeline $oPipeline): Interfaces\Unit
    {
        $this->oTarget = (object) [];

        /** @var Interfaces\Transformer $oTransformer */
        foreach ($oRecipe->yieldTransformers($oPipeline) as $oTransformer) {
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

        if ($sSourceProperty === null || empty($this->oSource)) {
            $this->getTarget()->{$sTargetProperty} = $oTransformer->transform(null, $this);

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
            $this->getTarget()->{$sTargetProperty} = $oTransformer->transform(
                $this->getSource()->{$sSourceProperty},
                $this
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
        return array_map(
            function ($mValue) {
                if ($mValue instanceof Callback) {
                    return (string) $mValue ?: null;
                }
                return $mValue;
            },
            (array) $this->oTarget
        );
    }
}
