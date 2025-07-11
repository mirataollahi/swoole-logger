<?php

namespace Craftix\Logger;

class Utils
{
    /** Convert non stringify variable to string */
    public static function stringify(mixed $value): string
    {
        return match (true) {
            is_scalar($value), $value === null => (string)$value,
            default => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        };
    }
}