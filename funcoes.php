<?php
declare(strict_types=1);

function gerarTokenCsrf(): string
{
 if (empty($_SESSION['csrf_token'])) {
 $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
 }
 return $_SESSION['csrf_token'];
}

function validarTokenCsrf(?string $token): bool
{
 return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function definirFlash(string $tipo, string $mensagem): void
{
 $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

function obterFlash(): ?array
{
 if (!empty($_SESSION['flash'])) {
 $flash = $_SESSION['flash'];
 unset($_SESSION['flash']);
 return $flash;
 }
 return null;
}

function formatarMoeda(float $valor): string
{
 return 'R$ ' . number_format($valor, 2, ',', '.');
}

function formatarDataBr(string $dataIso): string
{
 $dt = DateTime::createFromFormat('Y-m-d', $dataIso);
 return $dt ? $dt->format('d/m/Y') : $dataIso;
}

function classeStatus(string $status): string
{
 return match ($status) {
 'ativo' => 'status-ativo',
 'ferias' => 'status-ferias',
 'inativo' => 'status-inativo',
 default => '',
 };
}

function rotuloStatus(string $status): string
{
 return match ($status) {
 'ativo' => 'Ativo',
 'ferias' => 'Férias',
 'inativo' => 'Inativo',
 default => ucfirst($status),
 };
}
