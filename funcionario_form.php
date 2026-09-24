<?php
declare(strict_types=1);
session_start();

require 'init.php';
require 'crud.php';
require 'funcoes.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$modo = ($_GET['modo'] ?? '') === 'visualizar' ? 'visualizar' : ($id ? 'editar' : 'novo');
$somenteLeitura = $modo === 'visualizar';

$funcionario = [
'id' => null,
'nome' => '',
'cargo' => '',
'salario_base' => '',
'data_admissao' => '',
'status' => 'ativo',
];

if ($id) {

$registro = read($pdo, 'funcionarios', 'id = ' . $id);

if (!$registro) {
definirFlash('erro', 'Funcionário não encontrado.');
header('Location: funcionarios.php');
exit;
}
$funcionario = $registro;
}

$cargosPadrao = ['Mecânico','Financeiro','Estoquista','Atendente','Vendedor','Auxiliar Administrativo','Consultora de Peças'];
if ($funcionario['cargo'] !== '' && !in_array($funcionario['cargo'], $cargosPadrao, true)) {
$cargosPadrao[] = $funcionario['cargo'];
}

$titulo = match ($modo) {
'novo' => 'Novo Funcionário',
'editar' => 'Editar Funcionário',
'visualizar' => 'Detalhes do Funcionário',
};

$flash = obterFlash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="css/funcionarios.css">
<title><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?> | AutoPeças</title>
</head>
<body>
<div class="app">
<div class="main" style="width:100%;">
<header class="topbar">
<div class="page-title">
<h1><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h1>
<p>AutoPeças — Gestão de Pagamentos</p>
</div>
</header>

<main class="content">
<?php if ($flash): ?>
<div class="alerta alerta-<?= htmlspecialchars($flash['tipo'], ENT_QUOTES, 'UTF-8') ?>" style="max-width:640px;margin:0 auto 18px;">
<?= htmlspecialchars($flash['mensagem'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<div class="table-card" style="max-width:640px;margin:0 auto;padding:28px;">
<form method="post" action="funcionario_salvar.php">
<input type="hidden" name="csrf_token" value="<?= gerarTokenCsrf() ?>">
<?php if ($funcionario['id']): ?>
<input type="hidden" name="id" value="<?= (int)$funcionario['id'] ?>">
<?php endif; ?>

<div class="select-field" style="margin-bottom:16px;">
<label for="nome">Nome</label>
<input type="text" id="nome" name="nome" required maxlength="150"
value="<?= htmlspecialchars((string)$funcionario['nome'], ENT_QUOTES, 'UTF-8') ?>"
<?= $somenteLeitura ? 'disabled' : '' ?>
style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;">
</div>

<div class="select-field" style="margin-bottom:16px;">
<label for="cargo">Cargo</label>
<select id="cargo" name="cargo" required <?= $somenteLeitura ? 'disabled' : '' ?>>
<?php foreach ($cargosPadrao as $opcao): ?>
<option <?= $funcionario['cargo'] === $opcao ? 'selected' : '' ?>><?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; ?>
</select>
</div>

<div class="select-field" style="margin-bottom:16px;">
<label for="salario_base">Salário base (R$)</label>
<input type="number" id="salario_base" name="salario_base" step="0.01" min="0" required
value="<?= htmlspecialchars((string)$funcionario['salario_base'], ENT_QUOTES, 'UTF-8') ?>"
<?= $somenteLeitura ? 'disabled' : '' ?>
style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;">
</div>

<div class="select-field" style="margin-bottom:16px;">
<label for="data_admissao">Data de admissão</label>
<input type="date" id="data_admissao" name="data_admissao" required
value="<?= htmlspecialchars((string)$funcionario['data_admissao'], ENT_QUOTES, 'UTF-8') ?>"
<?= $somenteLeitura ? 'disabled' : '' ?>
style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;">
</div>

<div class="select-field" style="margin-bottom:24px;">
<label for="status">Situação</label>
<select id="status" name="status" required <?= $somenteLeitura ? 'disabled' : '' ?>>
<?php foreach (['ativo' => 'Ativo', 'ferias' => 'Férias', 'inativo' => 'Inativo'] as $valor => $rotulo): ?>
<option value="<?= $valor ?>" <?= $funcionario['status'] === $valor ? 'selected' : '' ?>><?= $rotulo ?></option>
<?php endforeach; ?>
</select>
</div>

<div style="display:flex;gap:12px;">
<a href="funcionarios.php" class="btn-outline">VOLTAR</a>
<?php if (!$somenteLeitura): ?>
<button type="submit" class="btn-primary"><?= $funcionario['id'] ? 'SALVAR ALTERAÇÕES' : 'CADASTRAR' ?></button>
<?php endif; ?>
</div>
</form>
</div>
</main>
</div>
</div>
</body>
</html>

