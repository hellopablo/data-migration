<?php

namespace HelloPablo\DataMigration;

use HelloPablo\DataMigration\Interfaces\Pipeline;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Manager
 *
 * @package HelloPablo\DataMigration
 */
class Manager
{
    /**
     * Runs the supplied migration Pipelines
     *
     * @param Pipeline[]           $aPipelines The Pipelines to run
     * @param OutputInterface|null $oOutput    An OutputInterface to log to
     *
     * @return $this
     */
    public function run(array $aPipelines, OutputInterface $oOutput = null): self
    {
        print_r($aPipelines);
        print_r($oOutput);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Runs the supplied migration Pipelines (as a dry-run)
     *
     * @param Pipeline[]           $aPipelines The Pipelines to run
     * @param OutputInterface|null $oOutput    An OutputInterface to log to
     *
     * @return $this
     */
    public function dryRun(array $aPipelines, OutputInterface $oOutput = null): self
    {
        print_r('DRY RUN');
        print_r($aPipelines);
        print_r($oOutput);

        return $this;
    }
}
