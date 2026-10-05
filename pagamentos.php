<?php
require_once __DIR__ . '/auth.php';
exigirLogin();

include 'conexao.php'; // cria $conexao (mysqli)

$sql = "
    SELECT
        pagamentos.id,
        funcionarios.nome AS funcionario,
        funcionarios.cargo,
        pagamentos.valor,
        pagamentos.data_pagamento,
        pagamentos.tipo,
        pagamentos.forma_pagamento,
        pagamentos.status
    FROM pagamentos
    INNER JOIN funcionarios
        ON pagamentos.funcionario_id = funcionarios.id
    ORDER BY pagamentos.data_pagamento DESC
";

$resultado  = $conexao->query($sql);
$pagamentos = $resultado->fetch_all(MYSQLI_ASSOC);

$totalPago = $conexao->query("
    SELECT COALESCE(SUM(valor), 0)
    FROM pagamentos
    WHERE status = 'pago'
")->fetch_row()[0];

$totalPendente = $conexao->query("
    SELECT COALESCE(SUM(valor), 0)
    FROM pagamentos
    WHERE status = 'pendente'
")->fetch_row()[0];

$totalRealizados = $conexao->query("
    SELECT COUNT(*)
    FROM pagamentos
    WHERE status = 'pago'
")->fetch_row()[0];

$funcionariosPagos = $conexao->query("
    SELECT COUNT(DISTINCT funcionario_id)
    FROM pagamentos
    WHERE status = 'pago'
")->fetch_row()[0];

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title>Pagamentos - Autopeças</title>
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

                <h1>Pagamentos</h1>

                <p>
                    Registre, visualize e gerencie os pagamentos dos funcionários.
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

                    <h2>Pagamentos</h2>

                    <p>
                        Acompanhe todos os pagamentos realizados e pendentes.
                    </p>

                </div>

                <a
                    href="cadastrar_pagamento.php"
                    class="btn-primary"
                >

                    <span class="material-symbols-outlined">
                        add
                    </span>

                    REGISTRAR PAGAMENTO

                </a>

            </div>

            <div class="summary-cards">

                <div class="summary-card">

                    <div class="summary-icon red">

                        <span class="material-symbols-outlined">
                            attach_money
                        </span>

                    </div>

                    <div>

                        <span>
                            Total pago
                        </span>

                        <strong>
                            R$
                            <?= number_format(
                                $totalPago,
                                2,
                                ',',
                                '.'
                            ) ?>
                        </strong>

                    </div>

                </div>

                <div class="summary-card">

                    <div class="summary-icon orange">

                        <span class="material-symbols-outlined">
                            schedule
                        </span>

                    </div>

                    <div>

                        <span>
                            Pagamentos pendentes
                        </span>

                        <strong>
                            R$
                            <?= number_format(
                                $totalPendente,
                                2,
                                ',',
                                '.'
                            ) ?>
                        </strong>

                    </div>

                </div>

                <div class="summary-card">

                    <div class="summary-icon red">

                        <span class="material-symbols-outlined">
                            calendar_month
                        </span>

                    </div>

                    <div>

                        <span>
                            Pagamentos realizados
                        </span>

                        <strong>
                            <?= $totalRealizados ?>
                        </strong>

                    </div>

                </div>

                <div class="summary-card">

                    <div class="summary-icon orange">

                        <span class="material-symbols-outlined">
                            groups
                        </span>

                    </div>

                    <div>

                        <span>
                            Funcionários pagos
                        </span>

                        <strong>
                            <?= $funcionariosPagos ?>
                        </strong>

                    </div>

                </div>

            </div>

            <div class="table-card">

                <div class="table-scroll">

                    <table>

                        <thead>

                            <tr>

                                <th>ID</th>

                                <th>Funcionário</th>

                                <th>Cargo</th>

                                <th>Valor</th>

                                <th>Data do pagamento</th>

                                <th>Status</th>

                                <th>Forma de pagamento</th>

                                <th>Ações</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if (count($pagamentos) > 0) { ?>

                                <?php foreach ($pagamentos as $pagamento) { ?>

                                    <tr>

                                        <td class="id-col">
                                            PAG<?= str_pad(
                                                $pagamento['id'],
                                                3,
                                                '0',
                                                STR_PAD_LEFT
                                            ) ?>
                                        </td>

                                        <td>

                                            <div class="employee-cell">

                                                <div class="employee-avatar">

                                                    <?= strtoupper(
                                                        substr(
                                                            $pagamento['funcionario'],
                                                            0,
                                                            2
                                                        )
                                                    ) ?>

                                                </div>

                                                <?= htmlspecialchars(
                                                    $pagamento['funcionario']
                                                ) ?>

                                            </div>

                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                $pagamento['cargo']
                                            ) ?>
                                        </td>

                                        <td>
                                            R$
                                            <?= number_format(
                                                $pagamento['valor'],
                                                2,
                                                ',',
                                                '.'
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= date(
                                                'd/m/Y',
                                                strtotime(
                                                    $pagamento['data_pagamento']
                                                )
                                            ) ?>
                                        </td>

                                        <td>

                                            <span
                                                class="status-pill status-<?= $pagamento['status'] ?>"
                                            >

                                                <?= ucfirst(
                                                    $pagamento['status']
                                                ) ?>

                                            </span>

                                        </td>

                                        <td>
                                            <?= ucfirst(
                                                $pagamento['forma_pagamento']
                                            ) ?>
                                        </td>

                                        <td>

                                            <div class="actions">

                                                <a
                                                    href="visualizar_pagamento.php?id=<?= $pagamento['id'] ?>"
                                                    class="action-btn"
                                                    title="Visualizar"
                                                >

                                                    <span class="material-symbols-outlined">
                                                        visibility
                                                    </span>

                                                </a>

                                                <a
                                                    href="editar_pagamento.php?id=<?= $pagamento['id'] ?>"
                                                    class="action-btn"
                                                    title="Editar"
                                                >

                                                    <span class="material-symbols-outlined">
                                                        edit
                                                    </span>

                                                </a>

                                                <a
                                                    href="excluir_pagamento.php?id=<?= $pagamento['id'] ?>"
                                                    class="action-btn delete"
                                                    title="Excluir"
                                                >

                                                    <span class="material-symbols-outlined">
                                                        delete
                                                    </span>

                                                </a>

                                            </div>

                                        </td>

                                    </tr>

                                <?php } ?>

                            <?php } else { ?>

                                <tr>

                                    <td
                                        colspan="8"
                                        class="empty"
                                    >
                                        Nenhum pagamento cadastrado.
                                    </td>

                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>

                <div class="table-footer">

                    <span>
                        Mostrando
                        <?= count($pagamentos) ?>
                        pagamentos
                    </span>

                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>
