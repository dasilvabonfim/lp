<?php

declare(strict_types=1);

namespace MiniForm\Database;

use PDO;

final class Connection
{
    public static function sqlite(string $path): PDO
    {
        $directory = dirname($path);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException("Não foi possível criar o diretório {$directory}.");
        }

        return new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}

