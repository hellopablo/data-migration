<?php

namespace HelloPablo\DataMigration\Transformer;

use HelloPablo\DataMigration\Exception\DataMigrationException;
use HelloPablo\DataMigration\IdMapper;
use HelloPablo\DataMigration\Interfaces;

/**
 * Class Id
 *
 * @package HelloPablo\DataMigration\Transformer
 */
class Id extends Copy
{
    /** @var string */
    protected $sPipeline;

    // --------------------------------------------------------------------------

    public function __construct(string $sSourceProperty = null, string $sTargetProperty = null, string $sPipeline = null)
    {
        parent::__construct($sSourceProperty, $sTargetProperty);

        if (
            empty($sPipeline)
            || !class_exists($sPipeline)
            || !in_array(Interfaces\Pipeline::class, class_implements($sPipeline))
        ) {
            throw new DataMigrationException(
                'Pipeline must be in instance of ' . Interfaces\Pipeline::class
            );
        }

        $this->sPipeline = $sPipeline;
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
        $mInput = parent::transform($mInput, $oUnit);

        return IdMapper::get($this->sPipeline, $mInput);
    }
}
