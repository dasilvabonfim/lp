<?php

declare(strict_types=1);

namespace MiniForm\Form;

use MiniForm\Metadata\FieldMetadata;
use MiniForm\Support\Html;

final class TextareaRenderer extends AbstractFieldRenderer
{
    public function supports(string $type): bool
    {
        return $type === 'textarea';
    }

    public function render(FieldMetadata $field, mixed $value, ?string $error): string
    {
        return sprintf(
            '<div class="field field-wide">%s<textarea %s rows="4">%s</textarea>%s</div>',
            $this->label($field),
            $this->attributes($field, $error),
            Html::escape($value),
            $this->error($field, $error),
        );
    }
}

