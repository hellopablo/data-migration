<?php

namespace HelloPablo\DataMigration;

use HelloPablo\DataMigration\Interfaces\Connector;
use HelloPablo\DataMigration\Interfaces\Pipeline;
use HelloPablo\DataMigration\Interfaces\Unit;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\Mocks\Objects\DataTypeOne1;

/**
 * Class Manager
 *
 * @package HelloPablo\DataMigration
 */
class Manager
{
    /** @var bool */
    protected $bDryRun = false;

    /** @var OutputInterface|null */
    protected $oOutputInterface;

    // --------------------------------------------------------------------------

    /**
     * Set the dry-run mode
     *
     * @param bool $bDryRun Whether to turn on dry-run mode or not
     *
     * @return $this
     */
    public function setDryRun(bool $bDryRun): self
    {
        $this->bDryRun = $bDryRun;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Whether the system is in dry run mode or not
     *
     * @return bool
     */
    public function isDryRun(): bool
    {
        return $this->bDryRun;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the output interface to use
     *
     * @param OutputInterface $oOutput The OutputInterface to use
     *
     * @return $this
     */
    public function setOutputInterface(OutputInterface $oOutput): self
    {
        $this->oOutputInterface = $oOutput;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the active OutputInterface
     *
     * @return OutputInterface|null
     */
    public function getOutputInterface(): ?OutputInterface
    {
        return $this->oOutputInterface;
    }

    // --------------------------------------------------------------------------

    /**
     * Writes a string to the OutputInterface
     *
     * @param string $sLine      The string to write
     * @param int    $iVerbosity The verbosity to write at
     *
     * @return $this
     */
    public function log($sLine = '', $iVerbosity = OutputInterface::VERBOSITY_NORMAL): self
    {
        if ($this->oOutputInterface && $this->oOutputInterface->getVerbosity() >= $iVerbosity) {
            $this->oOutputInterface->write($sLine);
        }
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Writes a lie to the OutputInterface
     *
     * @param string $sLine      The line to write
     * @param int    $iVerbosity The verbosity to write at
     *
     * @return $this
     */
    public function logln($sLine = '', $iVerbosity = OutputInterface::VERBOSITY_NORMAL): self
    {
        if ($this->oOutputInterface && $this->oOutputInterface->getVerbosity() >= $iVerbosity) {
            $this->oOutputInterface->writeln($sLine);
        }
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Runs the supplied migration Pipelines
     *
     * @param Pipeline[] $aPipelines The Pipelines to run
     *
     * @return $this
     */
    public function run(array $aPipelines): self
    {
        foreach ($aPipelines as $oPipeline) {
            $this->execute($oPipeline);
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Executes a Pipeline
     *
     * @param Pipeline $oPipeline The pipeline to execute
     *
     * @return $this
     */
    protected function execute(Pipeline $oPipeline): self
    {
        $this->logln('Executing pipeline: <info>' . get_class($oPipeline) . '</info>');

        $oConnectorSource = $oPipeline->getSourceConnector();
        $oConnectorTarget = $oPipeline->getTargetConnector();

        $this
            ->logln('Using source connector: <info>' . get_class($oConnectorSource) . '</info>', OutputInterface::VERBOSITY_VERBOSE)
            ->logln('Using target connector: <info>' . get_class($oConnectorTarget) . '</info>', OutputInterface::VERBOSITY_VERBOSE);

        $this
            ->connectConnector($oConnectorSource, 'source')
            ->connectConnector($oConnectorTarget, 'target');

        foreach ($oConnectorSource->read() as $oUnit) {

            if (!$oUnit instanceof Unit) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expected %s, got %s',
                        Unit::class,
                        gettype($oUnit)
                    )
                );
            }

            //  @todo (Pablo - 2020-06-16) - Process the unit

        }

        $this
            ->logln('Completed pipeline: <info>' . get_class($oPipeline) . '</info>')
            ->logln();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Connects a connector
     *
     * @param Connector $oConnector The connector to connect
     * @param string    $sLabel     The label to give the connector (for logging purposes)
     *
     * @return $this
     * @throws \Exception
     */
    protected function connectConnector(Connector $oConnector, string $sLabel): self
    {
        try {

            $this->log('Connecting to ' . $sLabel . ' connector... ', OutputInterface::VERBOSITY_VERBOSE);
            $oConnector->connect();
            $this->logln('<comment>connected</comment>', OutputInterface::VERBOSITY_VERBOSE);

        } catch (\Exception $e) {
            $this
                ->logln('<error>error</error>', OutputInterface::VERBOSITY_VERBOSE)
                ->logln('<error>' . $e->getMessage() . '</error>', OutputInterface::VERBOSITY_VERBOSE);
            throw $e;
        }

        return $this;
    }
}
