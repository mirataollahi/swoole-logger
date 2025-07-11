<?php

namespace Craftix\Enums;

/** Terminal colours (bright variants) */
enum LogColor: string
{
    case RESET = "\033[0m";
    case RED = "\033[31;1m";
    case GREEN = "\033[32;1m";
    case YELLOW = "\033[33;1m";
    case BLUE = "\033[34;1m";

    /** Return the ANSI escape sequence. */
    public function code(): string
    {
        return $this->value;
    }
}