<?php

namespace HelloPablo\DataMigration\Transformer;

use HelloPablo\DataMigration\Interfaces;

/**
 * Class Truncate
 *
 * @package HelloPablo\DataMigration\Transformer
 */
class Truncate extends Copy
{
    protected $iLength;
    protected $sEllipsis;

    // --------------------------------------------------------------------------

    /**
     * Truncate constructor.
     *
     * @param string|null $sSourceProperty
     * @param string|null $sTargetProperty
     * @param int         $iLength
     */
    public function __construct(
        string $sSourceProperty = null,
        string $sTargetProperty = null,
        int $iLength = 150,
        string $sEllipsis = '...'
    ) {
        parent::__construct($sSourceProperty, $sTargetProperty);
        $this->iLength   = $iLength;
        $this->sEllipsis = $sEllipsis;
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
        $aChunks = explode(' ', parent::transform($mInput, $oUnit));
        $sString = '';

        foreach ($aChunks as $sChunk) {
            $iLength = strlen($sString . ' ' . $sChunk . $this->sEllipsis);
            if ($iLength >= $this->iLength) {
                if ($iLength === $this->iLength) {
                    $sString .= ' ' . $sChunk;
                }

                if (preg_match('/[' . preg_quote('.?!', '/') . ']$/', $sString)) {
                    return $sString;

                } else {
                    return preg_replace('/[^a-zA-Z0-9\'"]$/', '', $sString) . $this->sEllipsis;
                }
            }

            $sString .= ' ' . $sChunk;
        }
    }
}
