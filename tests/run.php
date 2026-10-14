<?php

declare(strict_types=1);

use MiniForm\Database\SchemaGenerator;
use MiniForm\Form\FormBuilder;
use MiniForm\Metadata\ModelMetadata;
use MiniForm\Model\Produto;
use MiniForm\Repository\ModelRepository;
use MiniForm\Validation\Validator;

require dirname(__DIR__) . '/autoload.php';

$passed = 0;

function check(bool $condition, string $message): void
{
    global $passed;

    if (!$condition) {
        throw new RuntimeException('Falha: ' . $message);
    }

    $passed++;
    echo "[OK] {$message}\n";
}

$metadata = new ModelMetadata(Produto::class);
check($metadata->tableName() === 'produtos', 'Reflection encontra o nome da tabela');
check(count($metadata->fields()) === 6, 'Reflection encontra todos os campos');

$validator = new Validator();
$invalid = $validator->validate($metadata, []);
check(!$invalid->isValid(), 'Campos obrigatórios são validados');
check(isset($invalid->errors['nome'], $invalid->errors['preco']), 'Erros são associados aos campos');

$valid = $validator->validate($metadata, [
    'nome' => 'Livro de PHP',
    'categoria' => 'livros',
    'descricao' => 'Exemplo usado pelos testes automatizados.',
    'preco' => '79,90',
    'envio' => 'normal',
    'disponivel' => '1',
]);
check($valid->isValid(), 'Dados válidos passam pela validação');
check($valid->values['preco'] === 79.9, 'Número com vírgula é normalizado');

$form = (new FormBuilder())->render($metadata, new Produto(), [], [], 'token-de-teste');
check(str_contains($form, 'name="categoria"'), 'Formulário contém o campo select');
check(str_contains($form, 'type="radio"'), 'Formulário contém botões de opção');
check(str_contains($form, 'type="checkbox"'), 'Formulário contém caixa de seleção');

if (extension_loaded('pdo_sqlite')) {
    $pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $schema = new SchemaGenerator($pdo);
    $schema->create($metadata);
    $repository = new ModelRepository($pdo, $metadata);

    $product = new Produto();
    foreach ($metadata->fields() as $field) {
        $field->property->setValue($product, $valid->values[$field->name]);
    }
    $repository->save($product);
    check($product->id === 1, 'ORM insere e devolve o identificador');
    check(count($repository->all()) === 1, 'ORM recupera o registro salvo');
    $repository->delete(1);
    check($repository->all() === [], 'ORM exclui o registro');
} else {
    echo "[AVISO] pdo_sqlite não disponível; testes do repositório foram ignorados.\n";
}

echo "\n{$passed} verificações concluídas com sucesso.\n";

