<?php

namespace HelloPablo\DataMigration\Transformer;

use HelloPablo\DataMigration\Interfaces;

/**
 * Class StripHtml
 *
 * @package HelloPablo\DataMigration\Transformer
 */
class StripHtml extends Copy
{
    /**
     * Applies the transformation
     *
     * @param mixed $mInput The value to transform
     * @param Interfaces\Unit  $oUnit  The Unit being transformed
     */
    public function transform($mInput, Interfaces\Unit $oUnit)
    {
        return strip_tags(parent::transform($mInput, $oUnit));
    }
}
