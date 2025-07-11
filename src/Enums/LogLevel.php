<?php

namespace Craftix\Enums;

/** Log levels recognised by the CLI logger */
enum LogLevel: string
{
    case SUCCESS = 'SUCCESS';
    case INFO    = 'INFO';
    case WARNING = 'WARNING';
    case ERROR   = 'ERROR';

    /** Default colour for this level. */
    public function color(): LogColor
    {
        return match ($this) {
            self::SUCCESS => LogColor::GREEN,
            self::INFO    => LogColor::BLUE,
            self::WARNING => LogColor::YELLOW,
            self::ERROR   => LogColor::RED,
        };
    }
}