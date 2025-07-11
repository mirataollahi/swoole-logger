<?php

namespace Craftix\Logger;

use Craftix\Enums\LogLevel;
use Craftix\Printers\Base\LogPrinter;
use Craftix\Printers\Console\ConsoleLogPrinter;

/** Simple, high‑performance logger without I/O block */
class Logger
{
    /** The logger instance belongs to the service name */
    protected ?string $serviceName;

    /** Show logs in different color base log level */
    protected bool $enableColor = true;

    /** Enable print log message with its date  */
    protected bool $enableLogDateTime = true;

    /** Static logger service instance using in static log method call */
    public static self $staticLogger;

    /** Logs queue buffer manager to prevent I/O log operation */
    public static BufferManager $bufferManager;

    public bool $enableBuffer = false;

    /** @var LogPrinter[]  */
    public static array $printers = [];

    /** Create new instance of logger service */
    public function __construct(?string $serviceName = null, array $config = [])
    {
        $this->serviceName = $serviceName;
        $this->setConfigs($config);
        $this->intPrinters();
    }

    public function enableLogBuffer(): static
    {
        $this->enableBuffer = true;
        $this->initBufferManager();
        return $this;
    }

    /** Init logs buffer queue and its consumer */
    public function initBufferManager(): void
    {
        if (!isset(self::$bufferManager)) {
            self::$bufferManager = BufferManager::make();
        }
    }

    /** init printer and logs I/O Drivers */
    public function intPrinters(): void
    {
        self::$printers [ConsoleLogPrinter::class] = new ConsoleLogPrinter();
    }

    /** Print with printers */
    public static function onLogReceived(BufferedLog $bufferedLog): void
    {
        foreach (self::$printers as $printer) {
            $printer->print($bufferedLog);
        }
    }

    /** Create logger instance statically */
    public static function make(?string $name = null, array $config = []): static
    {
        return new static($name, $config);
    }

    /** Set logger instance configs */
    public function setConfigs(array $configs = []): void
    {
        foreach ($configs as $configName => $configValue) {
            if (property_exists($this, $configName)) {
                $this->$configName = $configValue;
            }
        }
    }

    /** Log a successful message with optional tags */
    public function success(string $message, array $tags = []): bool
    {
        $bufferedLog = $this->createBufferLog(LogLevel::SUCCESS, $message, $tags);
        return $this->pushInBuffer($bufferedLog);
    }

    /** Log an info message with optional tags */
    public function info(string $message, array $tags = []): bool
    {
        $bufferedLog = $this->createBufferLog(LogLevel::INFO, $message, $tags);
        return $this->pushInBuffer($bufferedLog);
    }

    /** Log a success message with optional tags */
    public function warning(string $message, array $tags = []): bool
    {
        $bufferedLog = $this->createBufferLog(LogLevel::WARNING, $message, $tags);
        return $this->pushInBuffer($bufferedLog);
    }

    /** Log a error message in php running console */
    public function error(string $message, array $tags = []): bool
    {
        $bufferedLog = $this->createBufferLog(LogLevel::ERROR, $message, $tags);
        return $this->pushInBuffer($bufferedLog);
    }

    public function pushInBuffer(BufferedLog $bufferedLog): bool
    {
        ## Buffer is disable
        if (!$this->enableBuffer) {
            self::onLogReceived($bufferedLog);
            return true;
        }

        ## Push to buffer
        return self::$bufferManager->push($bufferedLog);
    }

    /** Generate log message output format stream text */
    private function createBufferLog(LogLevel $level, string $message, array $tags = []): BufferedLog
    {
        return BufferedLog::create()
            ->setMessage($message)
            ->setLogLevel($level)
            ->setTags($tags)
            ->setServiceName($this->serviceName);
    }

    /** Static log a message in console stdout */
    public static function log(string $message, LogLevel $level = LogLevel::INFO): void
    {
        if (!isset(self::$staticLogger)) {
            self::$staticLogger = static::make();
        }
        match ($level) {
            LogLevel::SUCCESS => self::$staticLogger->success($message),
            LogLevel::WARNING => self::$staticLogger->warning($message),
            LogLevel::ERROR => self::$staticLogger->error($message),
            default => self::$staticLogger->info($message)
        };
    }
}