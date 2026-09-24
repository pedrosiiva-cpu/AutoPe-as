<?php
declare(strict_types=1);

function create(PDO $pdo, $table, array $data) {
$columns = implode(', ', array_keys($data));
$placeholders = implode(', ', array_fill(0, count($data), '?'));

$sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
$stmt = $pdo->prepare($sql);
$stmt->execute(array_values($data));
return $pdo->lastInsertId();
}

function readAll(PDO $pdo, $table, $where = null) {
$sql = "SELECT * FROM $table";
if ($where) {
$sql .= " WHERE $where";
}
$stmt = $pdo->query($sql);
return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para ler um registro
function read(PDO $pdo, $table, $where = null) {
$sql = "SELECT * FROM $table";
if ($where) {
$sql .= " WHERE $where";
}
$stmt = $pdo->query($sql);
return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para atualizar um registro
function update(PDO $pdo, $table, array $data, $where) {
$set = [];
foreach ($data as $column => $value) {
$set[] = "$column = ?";
}
$set = implode(', ', $set);

$sql = "UPDATE $table SET $set WHERE $where";
$stmt = $pdo->prepare($sql);
$stmt->execute(array_values($data));
return $stmt->rowCount();
}

// Função para excluir um registro
function delete(PDO $pdo, $table, $where) {
$sql = "DELETE FROM $table WHERE $where";
$stmt = $pdo->prepare($sql);
return $stmt->execute();
}

