<?php

namespace HelloPablo\DataMigration;

use HelloPablo\DataMigration\Exception\PipelineException\CommitException;
use HelloPablo\DataMigration\Exception\PipelineException\PrepareException;
use HelloPablo\DataMigration\Interfaces\Connector;
use HelloPablo\DataMigration\Interfaces\Pipeline;
use HelloPablo\DataMigration\Interfaces\Unit;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Manager
 *
 * @package HelloPablo\DataMigration
 */
class Manager
{
    const PROGRESS_BAR_FORMAT_TOTAL = ' [<info>%message%</info>]' . PHP_EOL . ' [%bar%] %percent:3s%% (%current%/%max%) – %remaining:6s% remaining' . PHP_EOL . PHP_EOL . ' [<info>Pipeline Progress</info>] ';
    const PROGRESS_BAR_FORMAT       = ' [%bar%] %percent:3s%% [<info>%message%</info>]';

    // --------------------------------------------------------------------------

    /** @var OutputInterface|null */
    protected $oOutputInterface;

    /** @var bool */
    protected $bDebug = false;

    /** @var bool */
    protected $bDryRun = false;

    /** @var bool */
    protected $bStopOnError = false;

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
     * Set error mode
     *
     * @param bool $bStopOnError Whether to turn stop on error, or summarrise errors
     *
     * @return $this
     */
    public function setStopOnError(bool $bStopOnError): self
    {
        $this->bStopOnError = $bStopOnError;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Whether the system will stop on error
     *
     * @return bool
     */
    public function isStopOnError(): bool
    {
        return $this->bStopOnError;
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
            $aProgressBars    = [
                'total' => $this->getProgressBar(0, 'Overall Progress', static::PROGRESS_BAR_FORMAT_TOTAL),
            ];

            foreach ($aPipelines as $oPipeline) {

                $oConnector = $oPipeline->getSourceConnector();
                $oConnector->connect();
                $iPipelineCount   = $oConnector->count();
                $iTotalOperations += $iPipelineCount;
                $oConnector->disconnect();

                $aProgressBars[get_class($oPipeline)] = $this->getProgressBar(
                    $iPipelineCount,
                    get_class($oPipeline)
                );
            }

            $aProgressBars['total']->setMaxSteps($iTotalOperations);

            foreach ($aProgressBars as $oProgressBar) {
                $oProgressBar->start();
            }
        }

        //  Prepare pipelines
        foreach ($aPipelines as $oPipeline) {
            $this->preparePipeline(
                $oPipeline,
                $aProgressBars['total'] ?? null,
                $aProgressBars[get_class($oPipeline)] ?? null
            );
        }

        if (!empty($aProgressBars['total'])) {
            $aProgressBars['total']->finish();
            $this->logln();
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    protected function getProgressBar(int $iTotalOperations, string $sMessage, string $sFormat = null): ProgressBar
    {
        $oProgressBar = new ProgressBar($this->getOutputInterface()->section(), $iTotalOperations);
        $oProgressBar->setMessage($sMessage);
        $oProgressBar->setFormat($sFormat ?? static::PROGRESS_BAR_FORMAT);
        return $oProgressBar;
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
            $aProgressBars    = [
                'total' => $this->getProgressBar(0, 'Overall Progress', static::PROGRESS_BAR_FORMAT_TOTAL),
            ];

            foreach ($aPipelines as $oPipeline) {
                $iPipelineCount   = $this->countLines($oPipeline);
                $iTotalOperations += $iPipelineCount;

                $aProgressBars[get_class($oPipeline)] = $this->getProgressBar(
                    $iPipelineCount,
                    get_class($oPipeline)
                );
            }

            if (empty($iTotalOperations)) {
                $this->logln('Nothing to commit.');
                return $this;
            } else {
                $aProgressBars['total']->setMaxSteps($iTotalOperations);
            }

            foreach ($aProgressBars as $oProgressBar) {
                $oProgressBar->start();
            }
        }

        foreach ($aPipelines as $oPipeline) {
            $this->commitPipeline(
                $oPipeline,
                $aProgressBars['total'] ?? null,
                $aProgressBars[get_class($oPipeline)] ?? null
            );
        }

        if (!empty($aProgressBars['total'])) {
            $aProgressBars['total']->finish();
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
     * @param Pipeline         $oPipeline         The pipeline to execute
     * @param ProgressBar|null $oTotalProgressBar The [total] progress bar object, if using one
     * @param ProgressBar|null $oTotalProgressBar The [pipeline] progress bar object, if using one
     *
     * @return $this
     */
    protected function preparePipeline(
        Pipeline $oPipeline,
        ?ProgressBar $oTotalProgressBar,
        ?ProgressBar $oSectionProgressBar
    ): self {

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

                $oException = (new PrepareException(
                    $e->getMessage(),
                    is_numeric($e->getCode()) ? $e->getCode() : null,
                    $e
                ))
                    ->setPipeline($oPipeline)
                    ->setUnit($oUnit);

                if ($this->isStopOnError()) {
                    throw $oException;
                } else {
                    $this->aPrepareErrors[] = $oException;
                }

            } finally {
                if ($oTotalProgressBar) {
                    $oTotalProgressBar->advance();
                }
                if ($oSectionProgressBar) {
                    $oSectionProgressBar->advance();
                }
            }
        }

        if (!empty($oSectionProgressBar)) {
            $oSectionProgressBar->finish();
        }

        $this->logln('', OutputInterface::VERBOSITY_VERBOSE);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Commits a Pipeline
     *
     * @param Pipeline         $oPipeline         The pipeline to commit
     * @param ProgressBar|null $oTotalProgressBar The [total] progress bar object, if using one
     * @param ProgressBar|null $oTotalProgressBar The [pipeline] progress bar object, if using one
     *
     * @return $this
     */
    protected function commitPipeline(
        Pipeline $oPipeline,
        ?ProgressBar $oTotalProgressBar,
        ?ProgressBar $oSectionProgressBar
    ): self {

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

        $oPipeline->commitStart();

        while (($sBuffer = fgets($this->aPipelineCache[$sPipeline])) !== false) {

            $oUnit = unserialize(str_replace('\\\n', "\n", $sBuffer));

            try {

                $oPipeline->commitBefore($oUnit);

                $this->log(' – Committing source item <info>#' . $oUnit->getSourceId() . '</info>... ', OutputInterface::VERBOSITY_VERBOSE);
                $oConnectorTarget->write($oUnit);
                $this->logln('<info>done</info>; target ID is <info>#' . $oUnit->getTargetId() . '</info>', OutputInterface::VERBOSITY_VERBOSE);

                IdMapper::add(
                    get_class($oPipeline),
                    $oUnit->getSourceId(),
                    $oUnit->getTargetId()
                );

                $oPipeline->commitAfter($oUnit);

            } catch (CommitException\SkipException $e) {

                $oPipeline->commitSkipped($oUnit, $e);

                $this->logln('<info>skipping</info>: ' . $e->getMessage(), OutputInterface::VERBOSITY_VERBOSE);

            } catch (\Exception $e) {

                $oPipeline->commitError($oUnit, $e);

                $this->logln('<error>' . $e->getMessage() . '</error>', OutputInterface::VERBOSITY_VERBOSE);

                $oException = (new CommitException(
                    sprintf(
                        '[%s] ID: %s – %s',
                        $sPipeline,
                        $oUnit->getSourceId(),
                        $e->getMessage()
                    ),
                    is_numeric($e->getCode()) ? $e->getCode() : null,
                    $e
                ))
                    ->setPipeline($oPipeline)
                    ->setUnit($oUnit);

                if ($this->isStopOnError()) {
                    throw $oException;
                } else {
                    $this->aCommitErrors[] = $oException;
                }

            } finally {
                if ($oTotalProgressBar) {
                    $oTotalProgressBar->advance();
                }
                if ($oSectionProgressBar) {
                    $oSectionProgressBar->advance();
                }
            }
        }

        $oPipeline->commitFinish($this->aCommitErrors);

        if (empty($this->aCommitErrors)) {
            //  @todo (Pablo - 2020-06-19) - Commit transaction, if supported
        } else {
            //  @todo (Pablo - 2020-06-19) - rollback transaction, if supported
        }

        if (!empty($oSectionProgressBar)) {
            $oSectionProgressBar->finish();
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
