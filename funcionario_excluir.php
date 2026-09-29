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

$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
try {

$sucesso = delete($pdo, 'funcionarios', 'id = ' . $id);

if ($sucesso) {
definirFlash('sucesso', 'Funcionário excluído com sucesso.');
} else {
definirFlash('erro', 'Funcionário não encontrado ou não excluído.');
}
} catch (PDOException $e) {
if ((int)$e->getCode() === 23000) {
definirFlash('erro', 'Não é possível excluir: este funcionário possui pagamentos ou prazos vinculados no sistema.');
} else {
definirFlash('erro', 'Erro ao excluir: ' . $e->getMessage());
}
}
}

header('Location: funcionarios.php');
exit;
