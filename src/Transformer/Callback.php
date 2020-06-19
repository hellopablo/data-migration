<?php

namespace HelloPablo\DataMigration\Transformer;

use HelloPablo\DataMigration\Exception\DataMigrationException;
use HelloPablo\DataMigration\Interfaces;

/**
 * Class Callback
 *
 * @package HelloPablo\DataMigration\Transformer
 */
class Callback extends Copy
{
    /** @var \Closure */
    protected $oCallback;

    // --------------------------------------------------------------------------

    /**
     * Callback constructor.
     *
     * @param string|null   $sSourceProperty
     * @param string|null   $sTargetProperty
     * @param \Closure|null $oCallback
     *
     * @throws DataMigrationException
     */
    public function __construct(
        string $sSourceProperty = null,
        string $sTargetProperty = null,
        \Closure $oCallback = null
    ) {
        parent::__construct($sSourceProperty, $sTargetProperty);
        if (!$oCallback instanceof \Closure) {
            throw new DataMigrationException(
                'Callback must be in instance of \Closure'
            );
        }
        $this->oCallback = $oCallback;
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
        return call_user_func($this->oCallback, $mInput, $oUnit);
    }
}
