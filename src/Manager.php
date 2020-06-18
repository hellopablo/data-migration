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

    /** @var string|null */
    protected $sCacheDir;

    /** @var OutputInterface|null */
    protected $oOutputInterface;

    /** @var string[] */
    protected $aPipelineCache = [];

    /** @var array */
    protected $aPrepareErrors;

    // --------------------------------------------------------------------------

    /**
     * Manager constructor.
     *
     * @param string|null $sCacheDir The cache directory to use
     */
    public function __construct(string $sCacheDir = null)
    {
        $this->setCacheDir($sCacheDir ?? sys_get_temp_dir());
    }

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
     * Sets the cache directory to use
     *
     * @param string $sCacheDir The cache directory to use
     *
     * @return $this
     */
    protected function setCacheDir(string $sCacheDir): self
    {
        $this->sCacheDir = rtrim($sCacheDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!is_dir($this->sCacheDir)) {
            if (!mkdir($this->sCacheDir, 0700, true)) {
                throw new \RuntimeException(
                    'Failed to create cache directory: ' . $this->sCacheDir
                );
            }
        }
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the cache directory being used
     *
     * @return string
     */
    protected function getCacheDir(): string
    {
        return $this->sCacheDir;
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
     * Prepares the supplied migration Pipelines
     *
     * @param Pipeline[] $aPipelines The Pipelines to run
     *
     * @return $this
     */
    public function prepare(array $aPipelines): self
    {
        $this->aPipelineCache = [];
        $this->aPrepareErrors = [];
        foreach ($aPipelines as $oPipeline) {
            $this->preparePipeline($oPipeline);
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Commits the supplied migration Pipelines
     *
     * @param Pipeline[] $aPipelines The Piplines to commit
     *
     * @return $this
     */
    public function commit(array $aPipelines)
    {
        foreach ($aPipelines as $oPipeline) {
            $this->commitPipeline($oPipeline);
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
    protected function preparePipeline(Pipeline $oPipeline): self
    {
        $sPipeline = get_class($oPipeline);
        $this->logln('Preparing pipeline: <info>' . $sPipeline . '</info>... ');

        $this->aPipelineCache[$sPipeline] = fopen($this->getCacheDir() . uniqid(), 'w+');

        $oConnectorSource = $oPipeline->getSourceConnector();
        $oRecipe          = $oPipeline->getRecipe();;

        $this->connectConnector($oConnectorSource);

        /** @var Unit $oUnit */
        foreach ($oConnectorSource->read() as $oUnit) {

            try {
                $this->log(' – Preparing source item <info>#' . $oUnit->getSourceId() . '</info>... ');

                if (!$oUnit instanceof Unit) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Expected %s, got %s',
                            Unit::class,
                            gettype($oUnit)
                        )
                    );
                }

                $oUnit
                    ->applyRecipe($oRecipe);

                //  @todo (Pablo - 2020-06-17) - Track the IDs

                fwrite(
                    $this->aPipelineCache[$sPipeline],
                    str_replace("\n", '\\\n', serialize($oUnit)) . PHP_EOL
                );

                $this->logln('<info>done</info>');

            } catch (\Exception $e) {
                $this->logln('<error>' . $e->getMessage() . '</error>');
                $this->aPrepareErrors[] = (object) [
                    'pipeline'  => $sPipeline,
                    'source_id' => $oUnit->getSourceId(),
                    'error'     => $e->getMessage(),
                ];
            }
        }

        $this->logln();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * @return array
     */
    public function getPrepareErrors(): array
    {
        return $this->aPrepareErrors;
    }

    // --------------------------------------------------------------------------

    /**
     * Commits a Pipeline
     *
     * @param Pipeline $oPipeline The pipeline to commit
     *
     * @return $this
     */
    protected function commitPipeline(Pipeline $oPipeline): self
    {
        $sPipeline = get_class($oPipeline);
        $this->log('Committing pipeline: <info>' . $sPipeline . '</info>... ');

        if ($this->isDryRun()) {
            return $this->logln('<warning>Dry Run - not comitting</warning>');
        }

        if (!array_key_exists(get_class($oPipeline), $this->aPipelineCache)) {
            return $this->log('<error>No cachefile available</error>');
        }

        $oConnectorTarget = $oPipeline->getTargetConnector();
        $this->connectConnector($oConnectorTarget);

        rewind($this->aPipelineCache[$sPipeline]);

        while (($buffer = fgets($this->aPipelineCache[$sPipeline])) !== false) {

            $oUnit = unserialize(str_replace('\\\n', "\n", $buffer));

            try {

                $this->log(' – Committing source item <info>#' . $oUnit->getSourceId() . '</info>... ');
                $oConnectorTarget->write($oUnit);
                $this->logln('<info>done</info>; target ID is <info>#' . $oUnit->getTargetId() . '</info>');

            } catch (\Exception $e) {
                $this->logln('<error>' . $e->getMessage() . '</error>');
            }
        }

        $this->logln();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Connects a connector
     *
     * @param Connector $oConnector The connector to connect
     *
     * @return $this
     * @throws \Exception
     */
    protected function connectConnector(Connector $oConnector): self
    {
        try {

            $oConnector->connect();

        } catch (\Exception $e) {
            $this
                ->logln('<error>error</error>')
                ->logln('<error>' . $e->getMessage() . '</error>');
            throw $e;
        }

        return $this;
    }
}
