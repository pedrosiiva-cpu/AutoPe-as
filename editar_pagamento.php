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

$funcionarios = readAll(
    $pdo,
    "funcionarios",
    "status = 'ativo'"
);

$usuarios = readAll(
    $pdo,
    "usuarios",
    "ativo = 1"
);

if (isset($_POST['editar'])) {

    $dados = [

        "funcionario_id" =>
            $_POST['funcionario_id'],

        "usuario_id" =>
            $_POST['usuario_id'],

        "valor" =>
            $_POST['valor'],

        "data_pagamento" =>
            $_POST['data_pagamento'],

        "tipo" =>
            $_POST['tipo'],

        "forma_pagamento" =>
            $_POST['forma_pagamento'],

        "status" =>
            $_POST['status'],

        "observacao" =>
            $_POST['observacao']

    ];

    update(
        $pdo,
        "pagamentos",
        $dados,
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

    <title>Editar Pagamento - Autopeças</title>

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

                <h1>Editar Pagamento</h1>

                <p>
                    Altere os dados do pagamento.
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

                    <h2>Editar pagamento</h2>

                    <p>
                        Atualize as informações necessárias.
                    </p>

                </div>

            </div>

            <div class="form-card">

                <div class="form-title">

                    <span class="material-symbols-outlined">
                        edit
                    </span>

                    <div>

                        <h3>
                            Dados do pagamento
                        </h3>

                        <p>
                            Pagamento #<?= $pagamento['id'] ?>
                        </p>

                    </div>

                </div>

                <form method="POST">

                    <div class="form-grid">

                        <div class="form-group">

                            <label>
                                Funcionário
                            </label>

                            <select
                                name="funcionario_id"
                                required
                            >

                                <?php foreach (
                                    $funcionarios
                                    as $funcionario
                                ) { ?>

                                    <option
                                        value="<?= $funcionario['id'] ?>"
                                        <?= $funcionario['id'] == $pagamento['funcionario_id']
                                            ? 'selected'
                                            : '' ?>
                                    >

                                        <?= htmlspecialchars(
                                            $funcionario['nome']
                                        ) ?>

                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                        <div class="form-group">

                            <label>
                                Usuário responsável
                            </label>

                            <select
                                name="usuario_id"
                                required
                            >

                                <?php foreach (
                                    $usuarios
                                    as $usuario
                                ) { ?>

                                    <option
                                        value="<?= $usuario['id'] ?>"
                                        <?= $usuario['id'] == $pagamento['usuario_id']
                                            ? 'selected'
                                            : '' ?>
                                    >

                                        <?= htmlspecialchars(
                                            $usuario['nome']
                                        ) ?>

                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                        <div class="form-group">

                            <label>
                                Valor
                            </label>

                            <input
                                type="number"
                                name="valor"
                                step="0.01"
                                value="<?= $pagamento['valor'] ?>"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label>
                                Data do pagamento
                            </label>

                            <input
                                type="date"
                                name="data_pagamento"
                                value="<?= $pagamento['data_pagamento'] ?>"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label>
                                Tipo
                            </label>

                            <select name="tipo">

                                <option
                                    value="salario"
                                    <?= $pagamento['tipo'] == 'salario'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Salário
                                </option>

                                <option
                                    value="bonus"
                                    <?= $pagamento['tipo'] == 'bonus'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Bônus
                                </option>

                                <option
                                    value="adiantamento"
                                    <?= $pagamento['tipo'] == 'adiantamento'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Adiantamento
                                </option>

                                <option
                                    value="vale"
                                    <?= $pagamento['tipo'] == 'vale'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Vale
                                </option>

                                <option
                                    value="outro"
                                    <?= $pagamento['tipo'] == 'outro'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Outro
                                </option>

                            </select>

                        </div>

                        <div class="form-group">

                            <label>
                                Forma de pagamento
                            </label>

                            <select name="forma_pagamento">

                                <option
                                    value="pix"
                                    <?= $pagamento['forma_pagamento'] == 'pix'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    PIX
                                </option>

                                <option
                                    value="transferencia"
                                    <?= $pagamento['forma_pagamento'] == 'transferencia'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Transferência
                                </option>

                                <option
                                    value="dinheiro"
                                    <?= $pagamento['forma_pagamento'] == 'dinheiro'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Dinheiro
                                </option>

                                <option
                                    value="outro"
                                    <?= $pagamento['forma_pagamento'] == 'outro'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Outro
                                </option>

                            </select>

                        </div>

                        <div class="form-group">

                            <label>
                                Status
                            </label>

                            <select name="status">

                                <option
                                    value="pendente"
                                    <?= $pagamento['status'] == 'pendente'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Pendente
                                </option>

                                <option
                                    value="pago"
                                    <?= $pagamento['status'] == 'pago'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Pago
                                </option>

                                <option
                                    value="atrasado"
                                    <?= $pagamento['status'] == 'atrasado'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Atrasado
                                </option>

                            </select>

                        </div>

                        <div class="form-group form-full">

                            <label>
                                Observação
                            </label>

                            <textarea
                                name="observacao"
                                rows="5"
                            ><?= htmlspecialchars(
                                $pagamento['observacao'] ?? ''
                            ) ?></textarea>

                        </div>

                    </div>

                    <div class="form-actions">

                        <a
                            href="pagamentos.php"
                            class="btn-cancel"
                        >
                            CANCELAR
                        </a>

                        <button
                            type="submit"
                            name="editar"
                            class="btn-save"
                        >
                            SALVAR ALTERAÇÕES
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>
</body>
</html>
