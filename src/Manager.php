<?php

namespace HelloPablo\DataMigration;

use HelloPablo\DataMigration\Interfaces\Connector;
use HelloPablo\DataMigration\Interfaces\Pipeline;
use HelloPablo\DataMigration\Interfaces\Unit;
use HelloPablo\DataMigration\Exception\PipelineException\CommitException;
use HelloPablo\DataMigration\Exception\PipelineException\PrepareException;
use Nails\Common\Service\Output;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Manager
 *
 * @package HelloPablo\DataMigration
 */
class Manager
{
    /** @var string */
    const PROGRESS_BAR_FORMAT = ' %current%/%max% [%bar%] %percent:3s%%; %remaining:6s% remaining; %memory:6s%' . PHP_EOL;

    // --------------------------------------------------------------------------

    /** @var OutputInterface|null */
    protected $oOutputInterface;

    /** @var bool */
    protected $bDebug = false;

    /** @var bool */
    protected $bDryRun = false;

    /** @var string|null */
    protected $sCacheDir;

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
     * @param string|null          $sCacheDir        The Cache directory to use
     * @param OutputInterface|null $oOutputInterface An OutputInterface to use
     * @param bool                 $bDebug           Whether to run in debug mode or not
     * @param bool                 $bDryRun          Whetehr to run in dry-run mode or not
     */
    public function __construct(
        string $sCacheDir = null,
        OutputInterface $oOutputInterface = null,
        bool $bDebug = false,
        bool $bDryRun = false
    ) {
        $this
            ->setCacheDir($sCacheDir ?: sys_get_temp_dir())
            ->setOutputInterface($oOutputInterface)
            ->setDebug($bDebug)
            ->setDryRun($bDryRun);
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the output interface to use
     *
     * @param OutputInterface|null $oOutput The OutputInterface to use
     *
     * @return $this
     */
    public function setOutputInterface(?OutputInterface $oOutput): self
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
     * Set the debug mode
     *
     * @param bool $bDebug Whether to turn on debug mode or not
     *
     * @return $this
     */
    public function setDebug(bool $bDebug): self
    {
        $this->bDebug = $bDebug;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Whether the system is in debug mode or not
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->bDebug;
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
        IdMapper::reset();
        $this->aPipelineCache = [];
        $this->aWarnings      = [];
        $this->aPrepareErrors = [];

        $this->sortPipelines($aPipelines);

        if ($this->getOutputInterface()->getVerbosity() === OutputInterface::VERBOSITY_NORMAL) {

            $this
                ->logln('Preparing pipelines...')
                ->logln();

            $iTotalOperations = 0;
            foreach ($aPipelines as $oPipeline) {
                $oConnector = $oPipeline->getSourceConnector();
                $oConnector->connect();
                $iTotalOperations += $oConnector->count();
                $oConnector->disconnect();
            }

            $oProgressBar = new ProgressBar($this->getOutputInterface(), $iTotalOperations);
            $oProgressBar->setFormat(static::PROGRESS_BAR_FORMAT);
            $oProgressBar->start();
        }

        foreach ($aPipelines as $oPipeline) {
            $this->preparePipeline($oPipeline, $oProgressBar ?? null);
        }

        if (!empty($oProgressBar)) {
            $oProgressBar->finish();
            $this->logln();
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

        $this->sortPipelines($aPipelines);

        if ($this->getOutputInterface()->getVerbosity() === OutputInterface::VERBOSITY_NORMAL) {

            $this
                ->logln('Committing pipelines...')
                ->logln();

            $iTotalOperations = 0;
            foreach ($aPipelines as $oPipeline) {
                $iTotalOperations += $this->countLines($oPipeline);
            }

            $oProgressBar = new ProgressBar($this->getOutputInterface(), $iTotalOperations);
            $oProgressBar->setFormat(static::PROGRESS_BAR_FORMAT);
            $oProgressBar->start();
        }

        foreach ($aPipelines as $oPipeline) {
            $this->commitPipeline($oPipeline, $oProgressBar ?? null);
        }

        if (!empty($oProgressBar)) {
            $oProgressBar->finish();
            $this->logln();
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Sorts pipelines by their priority
     *
     * @param array $aPipelines The Pipelines to sort
     */
    protected function sortPipelines(array &$aPipelines)
    {
        usort($aPipelines, function (Pipeline $oA, Pipeline $oB) {
            return $oA::getPriority() <=> $oB::getPriority();
        });
    }

    // --------------------------------------------------------------------------

    /**
     * Executes a Pipeline
     *
     * @param Pipeline         $oPipeline    The pipeline to execute
     * @param ProgressBar|null $oProgressBar The progress bar object, if using one
     *
     * @return $this
     */
    protected function preparePipeline(Pipeline $oPipeline, ?ProgressBar $oProgressBar): self
    {
        $sPipeline = get_class($oPipeline);
        $this->logln('Preparing pipeline: <info>' . $sPipeline . '</info>... ', OutputInterface::VERBOSITY_VERBOSE);

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

                }

                $this->log(
                    ' – Preparing source item <info>#' . $oUnit->getSourceId() . '</info>... ',
                    OutputInterface::VERBOSITY_VERBOSE
                );

                try {

                    $oUnit->shouldMigrate();

                } catch (\Exception $e) {
                    $this
                        ->logln('', OutputInterface::VERBOSITY_VERBOSE)
                        ->logln(
                            sprintf(
                                '   ↳  Item should not be migrated: <info>%s</info>',
                                $e->getMessage()
                            ),
                            OutputInterface::VERBOSITY_VERBOSE
                        );
                    continue;
                }

                $mMigratedId = $oUnit->isMigrated($oPipeline);

                if ($mMigratedId) {

                    $this
                        ->logln('', OutputInterface::VERBOSITY_VERBOSE)
                        ->logln(
                            sprintf(
                                '  ↳ Item lready migrated; target ID <info>#%s</info>',
                                $mMigratedId
                            ),
                            OutputInterface::VERBOSITY_VERBOSE
                        );

                    IdMapper::add(
                        get_class($oPipeline),
                        $oUnit->getSourceId(),
                        $mMigratedId
                    );

                    continue;
                }

                $oUnit
                    ->applyRecipe($oRecipe);

                if ($this->isDebug()) {
                    $this
                        ->logln()
                        ->logln('<bg=yellow;options=bold>DEBUG</>')
                        ->logln(print_r($oUnit, true));
                    die();
                }

                //  @todo (Pablo - 2020-06-17) - Track the IDs

                fwrite(
                    $this->aPipelineCache[$sPipeline],
                    str_replace("\n", '\\\n', serialize($oUnit)) . PHP_EOL
                );

                $this->logln('<info>done</info>', OutputInterface::VERBOSITY_VERBOSE);

            } catch (\Exception $e) {

                $this->logln('<error>' . $e->getMessage() . '</error>', OutputInterface::VERBOSITY_VERBOSE);

                $this->aPrepareErrors[] = (new PrepareException(
                    $e->getMessage(),
                    is_numeric($e->getCode()) ? $e->getCode() : null,
                    $e
                ))
                    ->setPipeline($oPipeline)
                    ->setUnit($oUnit);

            } finally {
                if ($oProgressBar) {
                    $oProgressBar->advance();
                }
            }
        }

        $this->logln('', OutputInterface::VERBOSITY_VERBOSE);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Commits a Pipeline
     *
     * @param Pipeline         $oPipeline    The pipeline to commit
     * @param ProgressBar|null $oProgressBar The progress bar object, if using one
     *
     * @return $this
     */
    protected function commitPipeline(Pipeline $oPipeline, ?ProgressBar $oProgressBar): self
    {
        $sPipeline = get_class($oPipeline);
        $this->logln('Committing pipeline: <info>' . $sPipeline . '</info>... ', OutputInterface::VERBOSITY_VERBOSE);

        if ($this->isDryRun()) {
            return $this
                ->logln('<bg=yellow;options=bold>Dry Run - not comitting</>', OutputInterface::VERBOSITY_VERBOSE);
        }

        if (!array_key_exists(get_class($oPipeline), $this->aPipelineCache)) {
            return $this->log('<error>No cachefile available</error>', OutputInterface::VERBOSITY_VERBOSE);
        }

        $oConnectorTarget = $oPipeline->getTargetConnector();
        $this->connectConnector($oConnectorTarget, 'target');

        rewind($this->aPipelineCache[$sPipeline]);

        //  @todo (Pablo - 2020-06-19) - Start a transaction, if supported

        while (($buffer = fgets($this->aPipelineCache[$sPipeline])) !== false) {

            $oUnit = unserialize(str_replace('\\\n', "\n", $buffer));

            try {

                $this->log(' – Committing source item <info>#' . $oUnit->getSourceId() . '</info>... ', OutputInterface::VERBOSITY_VERBOSE);
                $oConnectorTarget->write($oUnit);
                $this->logln('<info>done</info>; target ID is <info>#' . $oUnit->getTargetId() . '</info>', OutputInterface::VERBOSITY_VERBOSE);

                IdMapper::add(
                    get_class($oPipeline),
                    $oUnit->getSourceId(),
                    $oUnit->getTargetId()
                );

            } catch (\Exception $e) {
                $this->logln('<error>' . $e->getMessage() . '</error>', OutputInterface::VERBOSITY_VERBOSE);

                $this->aCommitErrors[] = (new CommitException(
                    $e->getMessage(),
                    is_numeric($e->getCode()) ? $e->getCode() : null,
                    $e
                ))
                    ->setPipeline($oPipeline)
                    ->setUnit($oUnit);
            } finally {
                if ($oProgressBar) {
                    $oProgressBar->advance();
                }
            }
        }

        if (empty($this->aCommitErrors)) {
            //  @todo (Pablo - 2020-06-19) - Commit transaction, if supported
        } else {
            //  @todo (Pablo - 2020-06-19) - rollback transaction, if supported
        }

        $this->logln('', OutputInterface::VERBOSITY_VERBOSE);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * counts the number of lines in a file
     *
     * @param $sFile The file to read
     *
     * @return int
     * @link https://stackoverflow.com/a/20537130
     */
    protected function countLines(Pipeline $oPipeline)
    {
        $sPipeline = get_class($oPipeline);

        rewind($this->aPipelineCache[$sPipeline]);

        $iLines = 0;

        while (!feof($this->aPipelineCache[$sPipeline])) {
            $iLines += substr_count(fread($this->aPipelineCache[$sPipeline], 8192), "\n");
        }

        return $iLines;
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

            $this->log(' – Connecting to ' . $sLabel . '... ', OutputInterface::VERBOSITY_VERBOSE);
            $oConnector->connect();
            $this->logln('<info>connected</info>', OutputInterface::VERBOSITY_VERBOSE);

        } catch (\Exception $e) {
            $this
                ->logln('<error>error</error>', OutputInterface::VERBOSITY_VERBOSE)
                ->logln('<error>' . $e->getMessage() . '</error>', OutputInterface::VERBOSITY_VERBOSE);
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
