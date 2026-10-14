<?php

declare(strict_types=1);

namespace MiniForm\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Field
{
    /**
     * @param array<string, string> $options
     */
    public function __construct(
        public string $label,
        public string $type = 'text',
        public string $sqlType = 'TEXT',
        public bool $required = false,
        public ?int $maxLength = null,
        public int|float|null $min = null,
        public int|float|null $max = null,
        public ?string $step = null,
        public array $options = [],
        public ?string $placeholder = null,
    ) {
    }
}

