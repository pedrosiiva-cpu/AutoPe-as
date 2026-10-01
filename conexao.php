<?php

$host = "localhost";
$port = 3306;
$dbname = "db_autopecas";
$username = "root";
$password = "";

$conexao = new mysqli($host, $username, $password, $dbname, $port);

if  ($conexao->connect_error) {
    die("Erro na conexão com o banco de dados.");
}

$conexao->set_charset("utf8");
?>