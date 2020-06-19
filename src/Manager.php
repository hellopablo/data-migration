<?php

namespace HelloPablo\DataMigration;

use HelloPablo\DataMigration\Interfaces\Connector;
use HelloPablo\DataMigration\Interfaces\Pipeline;
use HelloPablo\DataMigration\Interfaces\Unit;
use HelloPablo\DataMigration\Exception\PipelineException\CommitException;
use HelloPablo\DataMigration\Exception\PipelineException\PrepareException;
use Symfony\Component\Console\Output\OutputInterface;

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

    /** @var string[] */
    protected $aWarnings = [];

    /** @var PrepareException[] */
    protected $aPrepareErrors = [];

    /** @var CommitException[] */
    protected $aCommitErrors = [];

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
     * Checks the supplied migration Pipelines' connectors
     *
     * @param Pipeline[] $aPipelines The Pipelines to check
     *
     * @return $this
     */
    public function checkConnectors(array $aPipelines): self
    {
        $this->aWarnings = [];
        foreach ($aPipelines as $oPipeline) {

            $this->logln('Testing Pipeline <info>' . get_class($oPipeline) . '</info>');

            $this->logln('Testing source connector:');
            $oConnector = $oPipeline->getSourceConnector();

            try {

                $this->log(' – Can connect... ');
                $oConnector->connect();
                $this->logln('<info>yes</info>');
                $oConnector->disconnect();

            } catch (\Exception $e) {
                $this->logln('<info>no</info>');
                $this->aWarnings[] = sprintf(
                    '[<info>%s</info>] source connector failed to connect: %s',
                    get_class($oPipeline),
                    $e->getMessage()
                );
            }

            $this->logln('Testing target connector:');
            $oConnector = $oPipeline->getSourceConnector();

            try {

                $this->log(' – Can connect... ');
                $oConnector->connect();
                $this->logln('<info>yes</info>');
                $oConnector->disconnect();

            } catch (\Exception $e) {
                $this->logln('<info>no</info>');
                $this->aWarnings[] = sprintf(
                    '[<info>%s</info>] target connector failed to connect: %s',
                    get_class($oPipeline),
                    $e->getMessage()
                );
            }

            $this->log(' – Supports transactions... ');
            if ($oConnector->supportsTransactions()) {
                $this->logln('<info>yes</info>');
            } else {
                $this->logln('<info>no</info>');
                $this->aWarnings[] = sprintf(
                    '[<info>%s</info>] target connector does not support transactions, so errors cannot be rolled back automatically.',
                    get_class($oPipeline),
                );
            }
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
        $this->aWarnings      = [];
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
        $this->aWarnings     = [];
        $this->aCommitErrors = [];
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

        $this->connectConnector($oConnectorSource, 'source');

        /** @var Unit $oUnit */
        foreach ($oConnectorSource->read() as $oUnit) {

            try {

                if (!$oUnit instanceof Unit) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Expected %s, got %s',
                            Unit::class,
                            gettype($oUnit)
                        )
                    );

                } elseif (!$oUnit->shouldMigrate()) {
                    continue;
                }

                $this->log(' – Preparing source item <info>#' . $oUnit->getSourceId() . '</info>... ');

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

                $this->aPrepareErrors[] = (new PrepareException($e->getMessage(), $e->getCode(), $e))
                    ->setPipeline($oPipeline)
                    ->setUnit($oUnit);
            }
        }

        $this->logln();

        return $this;
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
        $this->logln('Committing pipeline: <info>' . $sPipeline . '</info>... ');

        if ($this->isDryRun()) {
            return $this->logln('<warning>Dry Run - not comitting</warning>');
        }

        if (!array_key_exists(get_class($oPipeline), $this->aPipelineCache)) {
            return $this->log('<error>No cachefile available</error>');
        }

        $oConnectorTarget = $oPipeline->getTargetConnector();
        $this->connectConnector($oConnectorTarget, 'target');

        rewind($this->aPipelineCache[$sPipeline]);

        //  @todo (Pablo - 2020-06-19) - Start a transaction, if supported

        while (($buffer = fgets($this->aPipelineCache[$sPipeline])) !== false) {

            $oUnit = unserialize(str_replace('\\\n', "\n", $buffer));

            try {

                $this->log(' – Committing source item <info>#' . $oUnit->getSourceId() . '</info>... ');
                $oConnectorTarget->write($oUnit);
                $this->logln('<info>done</info>; target ID is <info>#' . $oUnit->getTargetId() . '</info>');

            } catch (\Exception $e) {
                $this->logln('<error>' . $e->getMessage() . '</error>');

                $this->aCommitErrors[] = (new CommitException($e->getMessage(), $e->getCode(), $e))
                    ->setPipeline($oPipeline)
                    ->setUnit($oUnit);
            }
        }

        if (empty($this->aCommitErrors)) {
            //  @todo (Pablo - 2020-06-19) - Commit transaction, if supported
        } else {
            //  @todo (Pablo - 2020-06-19) - rollback transaction, if supported
        }

        $this->logln();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Connects a connector
     *
     * @param Connector $oConnector The connector to connect
     * @param string    $sLabel     The log friendly label for this connector
     *
     * @return $this
     * @throws \Exception
     */
    protected function connectConnector(Connector $oConnector, string $sLabel): self
    {
        try {

            $this->log(' – Connecting to ' . $sLabel . '... ');
            $oConnector->connect();
            $this->logln('<info>connected</info>');

        } catch (\Exception $e) {
            $this
                ->logln('<error>error</error>')
                ->logln('<error>' . $e->getMessage() . '</error>');
            throw $e;
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns any warnings which have been generated
     *
     * @return string[]
     */
    public function getWarnings(): array
    {
        return $this->aWarnings;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns any errors encountered during preparation
     *
     * @return PrepareException[]
     */
    public function getPrepareErrors(): array
    {
        return $this->aPrepareErrors;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns any errors encountered during commimt
     *
     * @return CommitException[]
     */
    public function getCommitErrors(): array
    {
        return $this->aCommitErrors;
    }
}
