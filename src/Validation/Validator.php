<?php

declare(strict_types=1);

namespace MiniForm\Validation;

use MiniForm\Metadata\ModelMetadata;

final class Validator
{
    public function validate(ModelMetadata $metadata, array $input): ValidationResult
    {
        $values = [];
        $errors = [];

        foreach ($metadata->fields() as $field) {
            $definition = $field->definition;
            $rawValue = $definition->type === 'checkbox'
                ? isset($input[$field->name])
                : trim((string) ($input[$field->name] ?? ''));

            if ($definition->required && $rawValue === '') {
                $errors[$field->name] = 'Este campo é obrigatório.';
                $values[$field->name] = $rawValue;
                continue;
            }

            if (is_string($rawValue) && $definition->maxLength !== null && $this->length($rawValue) > $definition->maxLength) {
                $errors[$field->name] = "Use no máximo {$definition->maxLength} caracteres.";
            }

            if ($definition->type === 'number' && $rawValue !== '') {
                $normalizedNumber = str_replace(',', '.', $rawValue);

                if (!is_numeric($normalizedNumber)) {
                    $errors[$field->name] = 'Informe um número válido.';
                } else {
                    $number = (float) $normalizedNumber;
                    if ($definition->min !== null && $number < $definition->min) {
                        $errors[$field->name] = "O valor mínimo é {$definition->min}.";
                    }
                    if ($definition->max !== null && $number > $definition->max) {
                        $errors[$field->name] = "O valor máximo é {$definition->max}.";
                    }
                    $rawValue = $number;
                }
            }

            if (in_array($definition->type, ['select', 'radio'], true) && $rawValue !== '') {
                if (!array_key_exists((string) $rawValue, $definition->options)) {
                    $errors[$field->name] = 'Selecione uma opção válida.';
                }
            }

            $values[$field->name] = $rawValue;
        }

        return new ValidationResult($values, $errors);
    }

    private function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}

