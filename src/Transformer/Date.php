<?php

namespace HelloPablo\DataMigration\Transformer;

use HelloPablo\DataMigration\Interfaces;

/**
 * Class Date
 *
 * @package HelloPablo\DataMigration\Transformer
 */
class Date extends Copy
{
    /**
     * The format for the output
     *
     * @var string
     */
    protected $sFormat = 'Y-m-d';

    /**
     * The default value to return
     *
     * @var \DateTime|null
     */
    protected $oDefault;

    /**
     * An array of common, but invalid, datetime formats
     *
     * @var stirng[]
     */
    protected $aInvalidFormats = [
        '',
        '0000-00-00',
        '0000-00-00 00:00:00',
    ];

    // --------------------------------------------------------------------------

    /**
     * Date constructor.
     *
     * @param string|null    $sSourceProperty
     * @param string|null    $sTargetProperty
     * @param \DateTime|null $oDefault
     */
    public function __construct(
        string $sSourceProperty = null,
        string $sTargetProperty = null,
        \DateTime $oDefault = null
    ) {
        parent::__construct($sSourceProperty, $sTargetProperty);
        $this->oDefault = $oDefault;
    }

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
     * @param mixed           $mInput The value to transform
     * @param Interfaces\Unit $oUnit  The Unit being transformed
     */
    public function transform($mInput, Interfaces\Unit $oUnit)
    {
        try {

            if (in_array($mInput, $this->aInvalidFormats)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        '"%s" is not a valid date format',
                        $mInput
                    )
                );
            } elseif (is_numeric($mInput)) {
                $oDate = new \DateTime('@' . parent::transform($mInput, $oUnit));
            } else {
                $oDate = new \DateTime(parent::transform($mInput, $oUnit));
            }

            return $oDate->format($this->sFormat);

        } catch (\Exception $e) {
            return $this->oDefault instanceof \DateTime
                ? $this->oDefault->format($this->sFormat)
                : null;
        }
    }
}
