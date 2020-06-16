<?php

namespace HelloPablo\DataMigration\Transformer;

use HelloPablo\DataMigration\Interfaces\Transformer;

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
     */
    public function transform($mInput)
    {
        return strip_tags(parent::transform($mInput));
    }
}
