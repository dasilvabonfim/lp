<?php

declare(strict_types=1);

namespace MiniForm\Form;

use MiniForm\Metadata\FieldMetadata;
use MiniForm\Support\Html;

final class InputRenderer extends AbstractFieldRenderer
{
    private const TYPES = ['text', 'email', 'number', 'date'];

    public function supports(string $type): bool
    {
        return in_array($type, self::TYPES, true);
    }

    public function render(FieldMetadata $field, mixed $value, ?string $error): string
    {
        return sprintf(
            '<div class="field">%s<input type="%s" %s value="%s">%s</div>',
            $this->label($field),
            Html::escape($field->definition->type),
            $this->attributes($field, $error),
            Html::escape($value),
            $this->error($field, $error),
        );
    }
}

