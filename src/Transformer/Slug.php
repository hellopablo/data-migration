<?php

namespace HelloPablo\DataMigration\Transformer;

use HelloPablo\DataMigration\Interfaces;

/**
 * Class Slug
 *
 * @package HelloPablo\DataMigration\Transformer
 */
class Slug extends Copy
{
    /**
     * Applies the transformation
     *
     * @param mixed           $mInput The value to transform
     * @param Interfaces\Unit $oUnit  The Unit being transformed
     */
    public function transform($mInput, Interfaces\Unit $oUnit)
    {
        $mInput = parent::transform($mInput, $oUnit);
        $mInput = strtolower($mInput);
        $mInput = preg_replace('/\'s|s\'/', 's', $mInput);
        $mInput = preg_replace('/[^a-z0-9]/', ' ', $mInput);
        $mInput = trim($mInput);
        $mInput = preg_replace('/ +/', '-', $mInput);

        return $mInput;
    }
}
