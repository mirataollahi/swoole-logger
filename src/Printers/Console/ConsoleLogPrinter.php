<?php

namespace Craftix\Printers\Console;

use Craftix\Enums\LogColor;
use Craftix\Enums\LogLevel;
use Craftix\Logger\BufferedLog;
use Craftix\Printers\Base\LogPrinter;
use Craftix\Logger\Utils;

/**
 * High performance async console terminal stdout logger printer driver class
 */
class ConsoleLogPrinter extends LogPrinter
{
    /**enable color in cli mode */
    public bool $enableColors;

    /** Enable print log message with its date  */
    protected bool $enableLogDateTime = true;

    public function __construct(bool $enableColors = true)
    {
        $this->enableColors = $enableColors;
    }

    /** Print fetched buffered log from logs buffer queue */
    public function print(BufferedLog $bufferedLog): void
    {
        $output = $this->makeOutput(
            level: $bufferedLog->logLevel ,
            message: $bufferedLog->message ,
            tags: $bufferedLog->tags
    );
        fwrite(STDOUT, $output);
    }

    /** Make final output stream base on buffered log information */
    private function makeOutput(LogLevel $level, string $message, array $tags = []): string
    {
        static $dateFormat = 'Y-m-d H:i:s';
        $timestamp = date($dateFormat);
        $tagStr = $this->formatTags($tags);
        $line = '';
        if ($this->enableColors) {
            $levelColor = $level->color()->code();
            $reset = LogColor::RESET->code();
            $line .= $levelColor;
            $line .= "[$timestamp] ";
            if (!empty($this->serviceName)) {
                $serviceBgColor = "\033[1;37;44m"; // Bold white text on blue background
                $line .= $serviceBgColor . ' ' . $this->serviceName . ' ' . $reset;
                $line .= $levelColor; // Re-apply level color
                $line .= ' ';
            }
            $line .= sprintf('[%s] %s%s', $level->value, $message, $tagStr);
            $line .= $reset; // Final reset
        } else {
            $service = !empty($this->serviceName) ? '[' . $this->serviceName . '] ' : '';
            $line = sprintf('[%s] %s[%s] %s%s', $timestamp, $service, $level->value, $message, $tagStr);
        }
        return $line . PHP_EOL;
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
                ? "{$k}=" . Utils::stringify($v)
                : Utils::stringify($v);
        }
        return ' {' . implode(', ', $parts) . '}';
    }
}