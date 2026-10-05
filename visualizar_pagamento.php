<?php
require_once __DIR__ . '/auth.php';
exigirLogin();

require_once 'init.php';

$id = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;

$sql = "
    SELECT
        pagamentos.*,
        funcionarios.nome AS funcionario,
        funcionarios.cargo,
        usuarios.nome AS usuario
    FROM pagamentos
    INNER JOIN funcionarios
        ON pagamentos.funcionario_id = funcionarios.id
    INNER JOIN usuarios
        ON pagamentos.usuario_id = usuarios.id
    WHERE pagamentos.id = $id
";

$stmt = $pdo->query($sql);

$pagamento = $stmt->fetch(
    PDO::FETCH_ASSOC
);

if (!$pagamento) {

    die("Pagamento não encontrado.");

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

    <title>Visualizar Pagamento - Autopeças</title>

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

                <h1>Visualizar Pagamento</h1>

                <p>
                    Consulte os detalhes do pagamento.
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

                    <span class="material-symbols-outlined">
                        expand_more
                    </span>

                </div>

            </div>

        </header>

        <section class="content">

            <div class="content-header">

                <div>

                    <h2>Detalhes do pagamento</h2>

                    <p>
                        Informações completas do registro.
                    </p>

                </div>

            </div>

            <div class="form-card">

                <div class="form-title">

                    <span class="material-symbols-outlined">
                        receipt_long
                    </span>

                    <div>

                        <h3>
                            Pagamento #<?= $pagamento['id'] ?>
                        </h3>

                        <p>
                            Dados registrados no sistema.
                        </p>

                    </div>

                </div>

                <div class="detalhes">

                    <p>
                        <strong>Funcionário:</strong>
                        <?= htmlspecialchars(
                            $pagamento['funcionario']
                        ) ?>
                    </p>

                    <p>
                        <strong>Cargo:</strong>
                        <?= htmlspecialchars(
                            $pagamento['cargo']
                        ) ?>
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

                    <p>
                        <strong>Tipo:</strong>
                        <?= ucfirst(
                            $pagamento['tipo']
                        ) ?>
                    </p>

                    <p>
                        <strong>Forma de pagamento:</strong>
                        <?= ucfirst(
                            $pagamento['forma_pagamento']
                        ) ?>
                    </p>

                    <p>
                        <strong>Status:</strong>

                        <span
                            class="status-pill status-<?= $pagamento['status'] ?>"
                        >

                            <?= ucfirst(
                                $pagamento['status']
                            ) ?>

                        </span>

                    </p>

                    <p>
                        <strong>Usuário responsável:</strong>
                        <?= htmlspecialchars(
                            $pagamento['usuario']
                        ) ?>
                    </p>

                    <p>
                        <strong>Observação:</strong>
                        <?= htmlspecialchars(
                            $pagamento['observacao'] ?? ''
                        ) ?>
                    </p>

                </div>

                <div class="form-actions">

                    <a
                        href="pagamentos.php"
                        class="btn-cancel"
                    >
                        VOLTAR
                    </a>

                    <a
                        href="editar_pagamento.php?id=<?= $pagamento['id'] ?>"
                        class="btn-save"
                    >
                        EDITAR PAGAMENTO
                    </a>

                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>
