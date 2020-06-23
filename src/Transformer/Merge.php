<?php

namespace HelloPablo\DataMigration\Transformer;

use HelloPablo\DataMigration\Exception\TransformerException\Merge\SourcePropertyDoesNotExistException;
use HelloPablo\DataMigration\Interfaces;

/**
 * Class Merge
 *
 * @package HelloPablo\DataMigration
 */
class Merge extends Copy
{
    /** @var Interfaces\Transformer[]|string[] */
    protected $aProperties = [];

    /** @var string */
    protected $sGlue;

    // --------------------------------------------------------------------------

    /**
     * Group constructor.
     *
     * @param string|null $sSourceProperty The source property
     * @param string|null $sTargetProperty The target property
     * @param string[]    $aProperties     An array of additional source properties to merge
     * @param string      $sGlue           The glue to join the properties together with
     */
    public function __construct(
        string $sSourceProperty = null,
        string $sTargetProperty = null,
        array $aProperties = [],
        string $sGlue = ' '
    ) {
        parent::__construct($sSourceProperty, $sTargetProperty);
        $this->aProperties = $aProperties;
        $this->sGlue       = $sGlue;
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
        $oSource = $oUnit->getSource();
        $aValues = [$mInput];
        foreach ($this->aProperties as $sProperty) {
            if (!property_exists($oSource, $sProperty)) {
                throw new SourcePropertyDoesNotExistException(
                    sprintf(
                        'Property "%s" does not exist on the source object',
                        $sProperty
                    )
                );
            }

            $aValues[] = $oSource->{$sProperty};
        }

        return implode($this->sGlue, $aValues);
    }
}
