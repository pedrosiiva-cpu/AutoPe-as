<?php
declare(strict_types=1);
session_start();

require 'init.php';
require 'crud.php';
require 'funcoes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
header('Location: funcionarios.php');
exit;
}

if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
definirFlash('erro', 'Sessão expirada. Tente novamente.');
header('Location: funcionarios.php');
exit;
}

$id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
$nome = trim((string)($_POST['nome'] ?? ''));
$cargo = trim((string)($_POST['cargo'] ?? ''));
$salarioBase = (string)($_POST['salario_base'] ?? '');
$dataAdmissao = (string)($_POST['data_admissao'] ?? '');
$status = (string)($_POST['status'] ?? 'ativo');

$erros = [];
if ($nome === '' || mb_strlen($nome) > 150) {
$erros[] = 'Informe um nome válido.';
}
if ($cargo === '' || mb_strlen($cargo) > 100) {
$erros[] = 'Informe o cargo.';
}
if (!is_numeric($salarioBase) || (float)$salarioBase < 0) {
$erros[] = 'Informe um salário válido.';
}
if (!DateTime::createFromFormat('Y-m-d', $dataAdmissao)) {
$erros[] = 'Informe uma data de admissão válida.';
}
if (!in_array($status, ['ativo', 'ferias', 'inativo'], true)) {
$erros[] = 'Situação inválida.';
}

if ($erros) {
definirFlash('erro', implode(' ', $erros));
header('Location: funcionario_form.php' . ($id ? "?id={$id}" : ''));
exit;
}

$dados = [
'nome' => $nome,
'cargo' => $cargo,
'salario_base' => $salarioBase,
'data_admissao' => $dataAdmissao,
'status' => $status
];

try {
if ($id) {

update($pdo, 'funcionarios', $dados, 'id = ' . $id);
definirFlash('sucesso', 'Funcionário atualizado com sucesso.');
} else {

create($pdo, 'funcionarios', $dados);
definirFlash('sucesso', 'Funcionário cadastrado com sucesso.');
}
} catch (PDOException $e) {
definirFlash('erro', 'Erro ao salvar: ' . $e->getMessage());
header('Location: funcionario_form.php' . ($id ? "?id={$id}" : ''));
exit;
}

header('Location: funcionarios.php');
exit;
