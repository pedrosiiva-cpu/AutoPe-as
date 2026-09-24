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

// Função para ler um registro único
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

=======

// Configurações de conexão com o banco de dados (ajuste conforme sua configuração)
$host = "localhost";
$port = 3306;
$dbname = "db_autopecas";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Função para inserir um novo registro
    function create($pdo, $table, array $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));
        return $pdo->lastInsertId();
    }

    // Função para ler registros
    function readAll($pdo, $table, $where = null) {
        $sql = "SELECT * FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function read($pdo, $table, $where = null) {
        $sql = "SELECT * FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $stmt = $pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Função para atualizar um registro
    function update($pdo, $table, array $data, $where) {
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
    function delete($pdo, $table, $where) {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute();
    }

} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
>>>>>>> 63e14c08a45388ec476731fbca7fd520fd7c3648
