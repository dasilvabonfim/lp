<?php

declare(strict_types=1);

namespace MiniForm\Form;

use MiniForm\Metadata\ModelMetadata;
use MiniForm\Model\Model;
use RuntimeException;

final class FormBuilder
{
    private array $renderers;

    public function __construct(?array $renderers = null)
    {
        $this->renderers = $renderers ?? [
            new InputRenderer(),
            new TextareaRenderer(),
            new SelectRenderer(),
            new ChoiceRenderer(),
        ];
    }

    public function render(
        ModelMetadata $metadata,
        Model $model,
        array $values,
        array $errors,
        string $csrfToken,
    ): string {
        $html = sprintf(
            '<form method="post" class="generated-form"><input type="hidden" name="csrf_token" value="%s"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="%s"><div class="form-grid">',
            \MiniForm\Support\Html::escape($csrfToken),
            \MiniForm\Support\Html::escape($model->id ?? ''),
        );

        foreach ($metadata->fields() as $field) {
            $value = array_key_exists($field->name, $values)
                ? $values[$field->name]
                : $field->property->getValue($model);
            $renderer = $this->rendererFor($field->definition->type);
            $html .= $renderer->render($field, $value, $errors[$field->name] ?? null);
        }

        $buttonLabel = $model->id === null ? 'Cadastrar produto' : 'Salvar alterações';
        $html .= sprintf(
            '</div><div class="form-actions"><button class="button button-primary" type="submit">%s</button>%s</div></form>',
            $buttonLabel,
            $model->id === null ? '' : '<a class="button button-ghost" href="index.php">Cancelar edição</a>',
        );

        return $html;
    }

    private function rendererFor(string $type): FieldRenderer
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($type)) {
                return $renderer;
            }
        }

        throw new RuntimeException("Nenhum renderizador registrado para o tipo {$type}.");
    }
}

