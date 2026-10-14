<?php

declare(strict_types=1);

namespace MiniForm\Repository;

use InvalidArgumentException;
use MiniForm\Metadata\ModelMetadata;
use MiniForm\Model\Model;
use PDO;
use ReflectionNamedType;

final class ModelRepository
{
    public function __construct(
        private readonly PDO $connection,
        private readonly ModelMetadata $metadata,
    ) {
    }

    public function all(): array
    {
        $statement = $this->connection->query(
            sprintf('SELECT * FROM "%s" ORDER BY "id" DESC', $this->metadata->tableName()),
        );

        return array_map(fn (array $row): Model => $this->hydrate($row), $statement->fetchAll());
    }

    public function find(int $id): ?Model
    {
        $statement = $this->connection->prepare(
            sprintf('SELECT * FROM "%s" WHERE "id" = :id', $this->metadata->tableName()),
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    public function save(Model $model): void
    {
        $expectedClass = $this->metadata->modelClass();

        if (!$model instanceof $expectedClass) {
            throw new InvalidArgumentException("O repositório aceita apenas instâncias de {$expectedClass}.");
        }

        $values = [];
        foreach ($this->metadata->fields() as $field) {
            $value = $field->property->getValue($model);
            $values[$field->name] = is_bool($value) ? (int) $value : $value;
        }

        if ($model->id === null) {
            $columns = array_map(static fn (string $name): string => '"' . $name . '"', array_keys($values));
            $parameters = array_map(static fn (string $name): string => ':' . $name, array_keys($values));
            $sql = sprintf(
                'INSERT INTO "%s" (%s) VALUES (%s)',
                $this->metadata->tableName(),
                implode(', ', $columns),
                implode(', ', $parameters),
            );
            $statement = $this->connection->prepare($sql);
            $statement->execute($values);
            $model->id = (int) $this->connection->lastInsertId();

            return;
        }

        $assignments = array_map(
            static fn (string $name): string => sprintf('"%s" = :%s', $name, $name),
            array_keys($values),
        );
        $sql = sprintf(
            'UPDATE "%s" SET %s WHERE "id" = :id',
            $this->metadata->tableName(),
            implode(', ', $assignments),
        );
        $values['id'] = $model->id;
        $statement = $this->connection->prepare($sql);
        $statement->execute($values);
    }

    public function delete(int $id): void
    {
        $statement = $this->connection->prepare(
            sprintf('DELETE FROM "%s" WHERE "id" = :id', $this->metadata->tableName()),
        );
        $statement->execute(['id' => $id]);
    }

    private function hydrate(array $row): Model
    {
        $class = $this->metadata->modelClass();
        $model = new $class();
        $model->id = (int) $row['id'];

        foreach ($this->metadata->fields() as $field) {
            $value = $row[$field->name];
            $type = $field->property->getType();
            $typeName = $type instanceof ReflectionNamedType ? $type->getName() : 'string';
            $value = match ($typeName) {
                'bool' => (bool) $value,
                'int' => (int) $value,
                'float' => (float) $value,
                default => (string) $value,
            };
            $field->property->setValue($model, $value);
        }

        return $model;
    }
}

