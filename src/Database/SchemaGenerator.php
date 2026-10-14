<?php

declare(strict_types=1);

namespace MiniForm\Database;

use MiniForm\Metadata\ModelMetadata;
use PDO;
use RuntimeException;

final class SchemaGenerator
{
    private const ALLOWED_TYPES = ['TEXT', 'INTEGER', 'REAL', 'BLOB', 'NUMERIC'];

    public function __construct(private readonly PDO $connection)
    {
    }

    public function create(ModelMetadata $metadata): void
    {
        $this->connection->exec($this->sql($metadata));
    }

    public function sql(ModelMetadata $metadata): string
    {
        $columns = ['"id" INTEGER PRIMARY KEY AUTOINCREMENT'];

        foreach ($metadata->fields() as $field) {
            $type = strtoupper($field->definition->sqlType);

            if (!in_array($type, self::ALLOWED_TYPES, true)) {
                throw new RuntimeException("Tipo SQL não permitido: {$type}");
            }

            $notNull = $field->definition->required ? ' NOT NULL' : '';
            $columns[] = sprintf('"%s" %s%s', $field->name, $type, $notNull);
        }

        return sprintf(
            "CREATE TABLE IF NOT EXISTS \"%s\" (\n    %s\n);",
            $metadata->tableName(),
            implode(",\n    ", $columns),
        );
    }
}

