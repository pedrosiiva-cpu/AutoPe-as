<?php
require_once __DIR__ . '/auth.php';
exigirLogin();

require_once 'init.php';
include 'crud.php';

$id = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;

$pagamento = read(
    $pdo,
    "pagamentos",
    "id = $id"
);

if (!$pagamento) {

    die("Pagamento não encontrado.");

}

if (isset($_POST['excluir'])) {

    delete(
        $pdo,
        "pagamentos",
        "id = $id"
    );

    header(
        "Location: pagamentos.php"
    );

    exit();

}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Excluir Pagamento - Autopeças</title>

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
    >

    <link
        rel="stylesheet"
        href="css/pagamentos.css"
    >

<link rel="stylesheet" href="css/sidebar.css">
</head>

<body>

<div class="app">

    <?php require __DIR__ . '/sidebar.php'; ?>

    <main class="main">

        <header class="topbar">

            <div class="menu-btn app-sidebar-toggle">

                <span class="material-symbols-outlined">
                    menu
                </span>

            </div>

            <div class="page-title">

                <h1>Excluir Pagamento</h1>

                <p>
                    Remova um registro de pagamento.
                </p>

            </div>

            <div class="topbar-actions">

                <div class="user-menu">

                    <div class="avatar">

                        <span class="material-symbols-outlined">
                            person
                        </span>

                    </div>

                    <div>

                        <div class="user-name">
                            Gestor Financeiro
                        </div>

                        <div class="user-role">
                            Administrador
                        </div>

                    </div>

                </div>

            </div>

        </header>

        <section class="content">

            <div class="content-header">

                <div>

                    <h2>Excluir pagamento</h2>

                    <p>
                        Confirme a exclusão do registro.
                    </p>

                </div>

            </div>

            <div class="form-card">

                <div class="form-title">

                    <span class="material-symbols-outlined">
                        delete
                    </span>

                    <div>

                        <h3>
                            Confirmar exclusão
                        </h3>

                        <p>
                            Esta ação removerá o pagamento.
                        </p>

                    </div>

                </div>

                <div class="detalhes">

                    <p>
                        <strong>ID:</strong>
                        <?= $pagamento['id'] ?>
                    </p>

                    <p>
                        <strong>Valor:</strong>
                        R$
                        <?= number_format(
                            $pagamento['valor'],
                            2,
                            ',',
                            '.'
                        ) ?>
                    </p>

                    <p>
                        <strong>Data:</strong>
                        <?= date(
                            'd/m/Y',
                            strtotime(
                                $pagamento['data_pagamento']
                            )
                        ) ?>
                    </p>

                </div>

                <form method="POST">

                    <div class="form-actions">

                        <a
                            href="pagamentos.php"
                            class="btn-cancel"
                        >
                            CANCELAR
                        </a>

                        <button
                            type="submit"
                            name="excluir"
                            class="btn-save"
                        >
                            SIM, EXCLUIR
                        </button>

                    </div>

                </form>

            </div>

        </section>

    </main>

</div>

</body>

</html>
