<?php

declare(strict_types=1);

namespace MiniForm\Metadata;

use MiniForm\Attributes\Field;
use ReflectionProperty;

final readonly class FieldMetadata
{
    public function __construct(
        public string $name,
        public ReflectionProperty $property,
        public Field $definition,
    ) {
    }
}

