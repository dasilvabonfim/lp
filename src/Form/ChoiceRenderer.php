<?php

declare(strict_types=1);

namespace MiniForm\Form;

use MiniForm\Metadata\FieldMetadata;
use MiniForm\Support\Html;

final class ChoiceRenderer extends AbstractFieldRenderer
{
    public function supports(string $type): bool
    {
        return in_array($type, ['checkbox', 'radio'], true);
    }

    public function render(FieldMetadata $field, mixed $value, ?string $error): string
    {
        if ($field->definition->type === 'checkbox') {
            $checked = (bool) $value ? ' checked' : '';

            return sprintf(
                '<div class="field field-choice"><label><input type="checkbox" %s value="1"%s><span>%s</span></label>%s</div>',
                $this->attributes($field, $error),
                $checked,
                Html::escape($field->definition->label),
                $this->error($field, $error),
            );
        }

        $choices = '';
        foreach ($field->definition->options as $optionValue => $label) {
            $id = $this->id($field) . '-' . $optionValue;
            $checked = (string) $value === (string) $optionValue ? ' checked' : '';
            $required = $field->definition->required ? ' required' : '';
            $choices .= sprintf(
                '<label class="choice"><input type="radio" id="%s" name="%s" value="%s"%s%s><span>%s</span></label>',
                Html::escape($id),
                Html::escape($field->name),
                Html::escape($optionValue),
                $checked,
                $required,
                Html::escape($label),
            );
        }

        return sprintf(
            '<fieldset class="field field-wide"><legend>%s%s</legend><div class="choices">%s</div>%s</fieldset>',
            Html::escape($field->definition->label),
            $field->definition->required ? ' <span aria-hidden="true">*</span>' : '',
            $choices,
            $this->error($field, $error),
        );
    }
}

