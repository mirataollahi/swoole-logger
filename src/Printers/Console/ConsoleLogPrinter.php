<?php

namespace App\Services\Printers\Console;

use App\Services\Logger\BufferedLog;
use App\Services\Logger\LogColor;
use App\Services\Logger\LogLevel;
use App\Services\Printers\Base\LogPrinter;

/**
 * High performance async console terminal stdout logger printer driver class
 */
class ConsoleLogPrinter extends LogPrinter
{

    /** Print fetched buffered log from logs buffer queue */
    public function print(BufferedLog $bufferedLog): void
    {
        $output = $this->makeOutput(
            level: $bufferedLog->logLevel;
        );
        fwrite(STDOUT, $message);
    }

    /** Make final output stream base on buffered log information */
    private function makeOutput(LogLevel $level, string $message, array $tags = []): string
    {
        static $dateFormat = 'Y-m-d H:i:s';
        $timestamp = date($dateFormat);
        $tagStr = $this->formatTags($tags);
        $line = '';
        if ($this->enableColor) {
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
}