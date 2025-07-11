<?php

namespace Craftix\Printers\Base;

use Craftix\Logger\BufferedLog;

/** Log printer driver class */
abstract class LogPrinter
{
    /** Create an instance of static log printer driver statically */
    public static function create(): static
    {
        return new static();
    }

    /** Print the given log message */
    abstract public function print(BufferedLog $bufferedLog): void;
}