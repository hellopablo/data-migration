<?php

namespace HelloPablo\DataMigration\Transformer;

use HelloPablo\DataMigration\Interfaces\Transformer;

/**
 * Class DateTime
 *
 * @package HelloPablo\DataMigration\Transformer
 */
class DateTime extends Date
{
    protected $sFormat = 'Y-m-d H:i:s';
}
