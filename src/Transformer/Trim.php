<?php

namespace HelloPablo\DataMigration\Transformer;

use HelloPablo\DataMigration\Interfaces;

/**
 * Class Trim
 *
 * @package HelloPablo\DataMigration\Transformer
 */
class Trim extends Copy
{
    /**
     * Applies the transformation
     *
     * @param mixed $mInput The value to transform
     * @param Interfaces\Unit  $oUnit  The Unit being transformed
     */
    public function transform($mInput, Interfaces\Unit $oUnit)
    {
        return trim(parent::transform($mInput, $oUnit));
    }
}
