<?php
declare(strict_types=1);

$host = "localhost";
$port = "3306";
$dbname = "sistema_pagamento_funcionarios";
$username = "root";
$password = "";

try {
 $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
 $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
 http_response_code(500);
 die("Erro de conexão: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
