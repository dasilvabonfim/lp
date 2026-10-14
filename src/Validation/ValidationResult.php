<?php

declare(strict_types=1);

namespace MiniForm\Validation;

final readonly class ValidationResult
{
    public function __construct(
        public array $values,
        public array $errors,
    ) {
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }
}

