<?php

namespace HelloPablo\DataMigration\Transformer;

use HelloPablo\DataMigration\Interfaces\Transformer;

/**
 * Class Date
 *
 * @package HelloPablo\DataMigration\Transformer
 */
class Date extends Copy
{
    protected $sFormat = 'Y-m-d';

    // --------------------------------------------------------------------------

    /**
     * Sets the format to use
     *
     * @param string $sFormat
     */
    public function setFormat(string $sFormat): self
    {
        $this->sFormat = $sFormat;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Applies the transformation
     *
     * @param mixed $mInput The value to transform
     */
    public function transform($mInput)
    {
        try {

            $oDate = new \DateTime(parent::transform($mInput));
            return $oDate->format($this->sFormat);

        } catch (\Exception $e) {
            return null;
        }
    }
}