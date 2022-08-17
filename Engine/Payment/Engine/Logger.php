<?php
namespace Aheadworks\Sarp2\Engine\Payment\Engine;

use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Pool;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Interface LoggerInterface
 * @package Aheadworks\Sarp2\Engine\Payment\Engine
 */
class Logger implements LoggerInterface
{
    /**
     * @var WriteInterface
     */
    private $directory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Pool
     */
    private $logDataFormatterPool;

    /**
     * @var string
     */
    private $file;

    /**
     * @param Filesystem $filesystem
     * @param Config $config
     * @param Pool $logDataFormatterPool
     * @param string $file
     */
    public function __construct(
        Filesystem $filesystem,
        Config $config,
        Pool $logDataFormatterPool,
        $file = 'aw_sarp2/engine.log'
    ) {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::LOG);
        $this->config = $config;
        $this->logDataFormatterPool = $logDataFormatterPool;
        $this->file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function log($str)
    {
        if ($this->config->isLogEnabled()) {
            $str = '## ' . date('Y-m-d H:i:s') . "\r\n" . $str . "\r\n";

            $stream = $this->directory->openFile($this->file, 'a');
            $stream->lock();
            $stream->write($str);
            $stream->unlock();
            $stream->close();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function traceSchedule($entryType, $data = [], $addData = [])
    {
        $formatter = $this->logDataFormatterPool->getFormatter(
            self::SOURCE_ACTION_SCHEDULE,
            $entryType
        );

        $logData = array_merge($data, $addData, ['entryType' => $entryType]);
        $this->log($formatter->format($logData));
    }

    /**
     * {@inheritdoc}
     */
    public function traceProcessing($entryType, $data = [], $addData = [])
    {
        $formatter = $this->logDataFormatterPool->getFormatter(
            self::SOURCE_ACTION_PROCESSING,
            $entryType
        );

        $logData = array_merge($data, $addData, ['entryType' => $entryType]);
        $this->log($formatter->format($logData));
    }
}
