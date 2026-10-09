<?php
// src/Core/Logger.php

namespace App\Core;

class Logger
{
    public static function write(string $level, string $message): void
    {
        $dir = __DIR__ . '/../../logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $line = sprintf(
            "[%s] %s: %s%s",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            PHP_EOL
        );

        file_put_contents($dir . '/app.log', $line, FILE_APPEND | LOCK_EX);
    }
}
