<?php

declare(strict_types=1);

use MiniForm\Database\Connection;
use MiniForm\Database\SchemaGenerator;
use MiniForm\Form\FormBuilder;
use MiniForm\Metadata\ModelMetadata;
use MiniForm\Model\Produto;
use MiniForm\Repository\ModelRepository;
use MiniForm\Security\Csrf;
use MiniForm\Support\Html;
use MiniForm\Validation\Validator;

require dirname(__DIR__) . '/autoload.php';

session_start();

$metadata = new ModelMetadata(Produto::class);
$connection = Connection::sqlite(dirname(__DIR__) . '/data/app.sqlite');
$schema = new SchemaGenerator($connection);
$schema->create($metadata);
$repository = new ModelRepository($connection, $metadata);
$validator = new Validator();
$formBuilder = new FormBuilder();

$model = new Produto();
$values = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::isValid($_POST['csrf_token'] ?? null)) {
        http_response_code(419);
        exit('A sessão expirou. Atualize a página e tente novamente.');
    }

    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'delete') {
        $repository->delete((int) ($_POST['id'] ?? 0));
        $_SESSION['flash'] = 'Produto removido com sucesso.';
        header('Location: index.php');
        exit;
    }

    if ($action === 'save') {
        $result = $validator->validate($metadata, $_POST);
        $values = $result->values;
        $errors = $result->errors;

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id !== false && $id !== null) {
            $model = $repository->find($id) ?? new Produto();
        }

        if ($result->isValid()) {
            foreach ($metadata->fields() as $field) {
                $field->property->setValue($model, $result->values[$field->name]);
            }

            $repository->save($model);
            $_SESSION['flash'] = $id ? 'Produto atualizado com sucesso.' : 'Produto cadastrado com sucesso.';
            header('Location: index.php');
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit'])) {
    $found = $repository->find((int) $_GET['edit']);
    if ($found !== null) {
        $model = $found;
    }
}

$products = $repository->all();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function optionLabel(ModelMetadata $metadata, string $fieldName, mixed $value): string
{
    foreach ($metadata->fields() as $field) {
        if ($field->name === $fieldName) {
            return $field->definition->options[(string) $value] ?? (string) $value;
        }
    }

    return (string) $value;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MiniForm PHP - Cadastro automático</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-content">
            <a class="brand" href="index.php" aria-label="MiniForm PHP - início">
                <span class="brand-mark">&lt;/&gt;</span>
                <span><strong>MiniForm</strong><small>PHP 8.2</small></span>
            </a>
            <span class="header-badge">Reflection + ORM</span>
        </div>
    </header>

    <main class="container page-grid">
        <section class="hero">
            <div>
                <p class="eyebrow">Mini-framework acadêmico</p>
                <h1>Um modelo. Formulário e banco gerados automaticamente.</h1>
                <p class="hero-copy">Os atributos da classe <code>Produto</code> definem os campos, as regras de validação e as colunas SQLite.</p>
            </div>
            <div class="stat-card">
                <strong><?= count($metadata->fields()) ?></strong>
                <span>campos descobertos por Reflection</span>
            </div>
        </section>

        <?php if ($flash !== null): ?>
            <div class="flash" role="status"><?= Html::escape($flash) ?></div>
        <?php endif; ?>

        <section class="card form-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow"><?= $model->id === null ? 'Novo registro' : 'Editando #' . $model->id ?></p>
                    <h2><?= $model->id === null ? 'Cadastrar produto' : 'Atualizar produto' ?></h2>
                </div>
                <span>Campos com * são obrigatórios</span>
            </div>
            <?= $formBuilder->render($metadata, $model, $values, $errors, Csrf::token()) ?>
        </section>

        <section class="card table-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Persistência automática</p>
                    <h2>Produtos cadastrados</h2>
                </div>
                <span><?= count($products) ?> registro(s)</span>
            </div>

            <?php if ($products === []): ?>
                <div class="empty-state">
                    <span aria-hidden="true">◇</span>
                    <strong>Nenhum produto cadastrado</strong>
                    <p>Preencha o formulário acima para testar o mini-framework.</p>
                </div>
            <?php else: ?>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Produto</th>
                                <th>Categoria</th>
                                <th>Preço</th>
                                <th>Status</th>
                                <th><span class="sr-only">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>#<?= $product->id ?></td>
                                <td><strong><?= Html::escape($product->nome) ?></strong><small><?= Html::escape($product->descricao) ?></small></td>
                                <td><?= Html::escape(optionLabel($metadata, 'categoria', $product->categoria)) ?></td>
                                <td>R$ <?= number_format($product->preco, 2, ',', '.') ?></td>
                                <td><span class="status <?= $product->disponivel ? 'status-on' : 'status-off' ?>"><?= $product->disponivel ? 'Disponível' : 'Indisponível' ?></span></td>
                                <td class="actions">
                                    <a class="icon-button" href="?edit=<?= $product->id ?>" aria-label="Editar <?= Html::escape($product->nome) ?>">Editar</a>
                                    <form method="post" onsubmit="return confirm('Remover este produto?');">
                                        <input type="hidden" name="csrf_token" value="<?= Html::escape(Csrf::token()) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $product->id ?>">
                                        <button class="icon-button danger" type="submit">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <details class="card technical-card">
            <summary>Ver SQL gerado pelo framework</summary>
            <pre><code><?= Html::escape($schema->sql($metadata)) ?></code></pre>
        </details>
    </main>

    <footer class="container footer">
        <span>UTFPR · Aspectos de Linguagens de Programação</span>
        <span>PHP · HTML · SQL</span>
    </footer>
</body>
</html>

