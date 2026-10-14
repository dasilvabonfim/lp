<?php

declare(strict_types=1);

namespace MiniForm\Form;

use MiniForm\Metadata\FieldMetadata;
use MiniForm\Support\Html;

final class SelectRenderer extends AbstractFieldRenderer
{
    public function supports(string $type): bool
    {
        return $type === 'select';
    }

    public function render(FieldMetadata $field, mixed $value, ?string $error): string
    {
        $options = '<option value="">Selecione</option>';

        foreach ($field->definition->options as $optionValue => $label) {
            $selected = (string) $value === (string) $optionValue ? ' selected' : '';
            $options .= sprintf(
                '<option value="%s"%s>%s</option>',
                Html::escape($optionValue),
                $selected,
                Html::escape($label),
            );
        }

        return sprintf(
            '<div class="field">%s<select %s>%s</select>%s</div>',
            $this->label($field),
            $this->attributes($field, $error),
            $options,
            $this->error($field, $error),
        );
    }
}

