<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function exigirLogin(): void
{
    if (empty($_SESSION['usuario'])) {
        header('Location: login.php');
        exit;
    }
}

function carregarUsuarios(): array
{
    $arquivo = __DIR__ . '/usuarios.json';
    if (!is_file($arquivo)) {
        return ['admin' => '123456'];
    }
    $usuarios = json_decode((string) file_get_contents($arquivo), true);
    return is_array($usuarios) ? $usuarios : [];
}

function salvarUsuarios(array $usuarios): bool
{
    return file_put_contents(__DIR__ . '/usuarios.json', json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX) !== false;
}
