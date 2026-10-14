<?php

declare(strict_types=1);

namespace MiniForm\Form;

use MiniForm\Metadata\FieldMetadata;

interface FieldRenderer
{
    public function supports(string $type): bool;

    public function render(FieldMetadata $field, mixed $value, ?string $error): string;
}

