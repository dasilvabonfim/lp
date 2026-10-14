<?php

declare(strict_types=1);

namespace MiniForm\Form;

use MiniForm\Metadata\FieldMetadata;
use MiniForm\Support\Html;

abstract class AbstractFieldRenderer implements FieldRenderer
{
    protected function id(FieldMetadata $field): string
    {
        return 'field-' . $field->name;
    }

    protected function label(FieldMetadata $field, bool $includeFor = true): string
    {
        $required = $field->definition->required ? ' <span aria-hidden="true">*</span>' : '';
        $for = $includeFor ? ' for="' . Html::escape($this->id($field)) . '"' : '';

        return sprintf('<label%s>%s%s</label>', $for, Html::escape($field->definition->label), $required);
    }

    protected function attributes(FieldMetadata $field, ?string $error): string
    {
        $definition = $field->definition;
        $attributes = [
            'id' => $this->id($field),
            'name' => $field->name,
        ];

        if ($definition->required) {
            $attributes['required'] = true;
        }
        if ($definition->maxLength !== null) {
            $attributes['maxlength'] = $definition->maxLength;
        }
        if ($definition->min !== null) {
            $attributes['min'] = $definition->min;
        }
        if ($definition->max !== null) {
            $attributes['max'] = $definition->max;
        }
        if ($definition->step !== null) {
            $attributes['step'] = $definition->step;
        }
        if ($definition->placeholder !== null) {
            $attributes['placeholder'] = $definition->placeholder;
        }
        if ($error !== null) {
            $attributes['aria-invalid'] = 'true';
            $attributes['aria-describedby'] = $this->id($field) . '-error';
        }

        return $this->serializeAttributes($attributes);
    }

    protected function error(FieldMetadata $field, ?string $error): string
    {
        if ($error === null) {
            return '';
        }

        return sprintf(
            '<p class="field-error" id="%s-error">%s</p>',
            Html::escape($this->id($field)),
            Html::escape($error),
        );
    }

    protected function serializeAttributes(array $attributes): string
    {
        $parts = [];

        foreach ($attributes as $name => $value) {
            if ($value === false) {
                continue;
            }

            $parts[] = $value === true
                ? Html::escape($name)
                : sprintf('%s="%s"', Html::escape($name), Html::escape($value));
        }

        return implode(' ', $parts);
    }
}

