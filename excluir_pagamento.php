<?php

include 'conexao.php';
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

</head>

<body>

<div class="app">

    <aside class="sidebar">

        <div class="brand">

            <div class="brand-icon">

                <span class="material-symbols-outlined">
                    directions_car
                </span>

            </div>

            <div class="brand-name">
                AUTOPEÇAS
            </div>

            <div class="brand-sub">
                GESTÃO DE PAGAMENTOS
            </div>

        </div>

        <nav class="nav-menu">

            <a href="dashboard.php" class="nav-link">

                <span class="material-symbols-outlined">
                    home
                </span>

                <span>Dashboard</span>

            </a>

            <a href="funcionarios.php" class="nav-link">

                <span class="material-symbols-outlined">
                    groups
                </span>

                <span>Funcionários</span>

            </a>

            <a
                href="pagamentos.php"
                class="nav-link active"
            >

                <span class="material-symbols-outlined">
                    payments
                </span>

                <span>Pagamentos</span>

            </a>

            <a
                href="prazos_alertas.php"
                class="nav-link"
            >

                <span class="material-symbols-outlined">
                    notifications
                </span>

                <span>Prazos e Alertas</span>

            </a>

            <a href="relatorios.php" class="nav-link">

                <span class="material-symbols-outlined">
                    bar_chart
                </span>

                <span>Relatórios</span>

            </a>

        </nav>

        <div class="nav-footer">

            <a href="index.php" class="nav-link logout">

                <span class="material-symbols-outlined">
                    logout
                </span>

                <span>Sair</span>

            </a>

        </div>

    </aside>

    <main class="main">

        <header class="topbar">

            <div class="menu-btn">

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