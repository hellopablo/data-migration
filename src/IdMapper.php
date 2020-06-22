<?php

namespace HelloPablo\DataMigration;

use HelloPablo\DataMigration\IdMapper\Callback;
use HelloPablo\DataMigration\Interfaces;

/**
 * Class IdMapper
 *
 * @package HelloPablo\DataMigration
 */
class IdMapper
{
    /**
     * The ID maps
     *
     * @var array
     */
    protected static $aMaps = [];

    // --------------------------------------------------------------------------

    /**
     * Resets the ID Mapper
     */
    public static function reset(): void
    {
        static::$aMaps = [];
    }

    // --------------------------------------------------------------------------

    /**
     * Records a new ID map
     *
     * @param string $oPipeline The pipeline to record against
     * @param mixed  $mSourceId The source ID
     * @param mixed  $mTargetId The target ID
     */
    public static function add(string $sPipeline, $mSourceId, $mTargetId): void
    {
        if (!array_key_exists($sPipeline, static::$aMaps)) {
            static::$aMaps[$sPipeline] = [];
        }

        static::$aMaps[$sPipeline][$mSourceId] = $mTargetId;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns a new IdMapper\Callback object
     *
     * @param string $sPipeline The pipeline to map
     * @param mixed  $mSourceId The source ID
     *
     * @return Callback
     */
    public static function get(string $sPipeline, $mSourceId): Callback
    {
        return new Callback($sPipeline, $mSourceId);
    }

    // --------------------------------------------------------------------------

    /**
     * Looksup a mapping
     *
     * @param string $sPipeline The pipeline to check
     * @param mixed  $mSourceId The source ID
     *
     * @return mixed|null
     */
    public static function lookup(string $sPipeline, $mSourceId)
    {
        return static::$aMaps[$sPipeline][$mSourceId] ?? null;
    }
}
