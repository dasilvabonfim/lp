<?php

declare(strict_types=1);

namespace MiniForm\Metadata;

use InvalidArgumentException;
use MiniForm\Attributes\Field;
use MiniForm\Attributes\Table;
use MiniForm\Model\Model;
use ReflectionClass;

final class ModelMetadata
{
    /** @var class-string<Model> */
    private string $modelClass;

    private string $tableName;

    /** @var list<FieldMetadata> */
    private array $fields = [];

    /**
     * @param class-string<Model> $modelClass
     */
    public function __construct(string $modelClass)
    {
        if (!is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException("{$modelClass} deve herdar de " . Model::class . '.');
        }

        $reflection = new ReflectionClass($modelClass);
        $tableAttributes = $reflection->getAttributes(Table::class);

        if ($tableAttributes === []) {
            throw new InvalidArgumentException("{$modelClass} precisa do atributo #[Table].");
        }

        $this->modelClass = $modelClass;
        $this->tableName = $tableAttributes[0]->newInstance()->name;
        $this->assertIdentifier($this->tableName);

        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(Field::class);

            if ($attributes === []) {
                continue;
            }

            $this->assertIdentifier($property->getName());
            $this->fields[] = new FieldMetadata(
                $property->getName(),
                $property,
                $attributes[0]->newInstance(),
            );
        }

        if ($this->fields === []) {
            throw new InvalidArgumentException("{$modelClass} não possui campos com #[Field].");
        }
    }

    /** @return class-string<Model> */
    public function modelClass(): string
    {
        return $this->modelClass;
    }

    public function tableName(): string
    {
        return $this->tableName;
    }

    /** @return list<FieldMetadata> */
    public function fields(): array
    {
        return $this->fields;
    }

    private function assertIdentifier(string $identifier): void
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier) !== 1) {
            throw new InvalidArgumentException("Identificador SQL inválido: {$identifier}");
        }
    }
}

