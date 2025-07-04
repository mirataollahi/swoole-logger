<?php

namespace App\Services\Logger;

use App\Services\Printers\Console\ConsoleLogPrinter;

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

    public static array $printers = [];

    /** Create new instance of logger service */
    public function __construct(?string $serviceName = null, array $config = [])
    {
        $this->serviceName = $serviceName;
        $this->setConfigs($config);
        $this->intPrinters();
    }

    /** Init logs buffer queue and its consumer */
    public function intBufferManager(): void
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
    public static function print(): void
    {
        foreach (self::$printers as $printer) {
            if (method_exists($printer, 'print')) {
                $printer->print();
            }
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
    public function success(string $message, array $tags = []): void
    {
        $bufferedLog = $this->createBufferLog(LogLevel::SUCCESS, $message, $tags);
        $this->echo($output);
    }

    /** Log an info message with optional tags */
    public function info(string $message, array $tags = []): void
    {
        $bufferedLog = $this->createBufferLog(LogLevel::INFO, $message, $tags);
        $this->echo($output);
    }

    /** Log a success message with optional tags */
    public function warning(string $message, array $tags = []): void
    {
        $bufferedLog = $this->createBufferLog(LogLevel::WARNING, $message, $tags);
        $this->echo($output);
    }

    /** Log a error message in php running console */
    public function error(string $message, array $tags = []): void
    {
        $bufferedLog = $this->createBufferLog(LogLevel::ERROR, $message, $tags);
        $this->echo($output);
    }

    /** Generate log message output format stream text */
    private function createBufferLog(LogLevel $level, string $message, array $tags = []): string
    {
        $bufferedLog = BufferedLog::create()
            ->setMessage($message)
            ->setLogLevel($level)
            ->setTags($tags)
            ->setServiceName($this->serviceName);
    }

    /** Echo message in terminal console  */
    public function echo(?string $outputStream): void
    {
        echo $outputStream . PHP_EOL;
    }

    /** Format log tags in printable stream */
    private function formatTags(array $tags): string
    {
        if (!$tags) {
            return '';
        }
        $parts = [];
        foreach ($tags as $k => $v) {
            $parts[] = is_string($k)
                ? "{$k}=" . $this->stringify($v)
                : $this->stringify($v);
        }
        return ' {' . implode(', ', $parts) . '}';
    }

    /** Convert non stringify variable to string */
    private function stringify(mixed $value): string
    {
        return match (true) {
            is_scalar($value), $value === null => (string)$value,
            default => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        };
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