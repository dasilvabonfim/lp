<?php

declare(strict_types=1);

namespace MiniForm\Validation;

final readonly class ValidationResult
{
    /**
     * @param array<string, mixed> $values
     * @param array<string, string> $errors
     */
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

