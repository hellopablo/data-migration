<?php

namespace HelloPablo\DataMigration\IdMapper;

use HelloPablo\DataMigration\IdMapper;

class Callback
{
    /** @var string */
    protected $sPipeline;

    /** @var mixed */
    protected $mSourceId;

    // --------------------------------------------------------------------------

    /**
     * Callback constructor.
     *
     * @param string $sPipeline The pipeline to check
     * @param mixed  $mSourceId The source ID to map
     */
    public function __construct(string $sPipeline, $mSourceId)
    {
        $this->sPipeline = $sPipeline;
        $this->mSourceId = $mSourceId;
    }

    // --------------------------------------------------------------------------

    /**
     * Perform the mapping
     */
    public function __toString()
    {
        //  @todo (Pablo - 2020-06-22) - Somehow support converting this to null on return
        return (string) IdMapper::lookup($this->sPipeline, $this->mSourceId);
    }
}
