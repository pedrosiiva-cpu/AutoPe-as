<?php
require_once __DIR__ . '/auth.php';
exigirLogin();

require_once 'init.php';
include 'crud.php';

$mensagem = "";

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

if (isset($_POST['cadastrar'])) {

    $funcionario_id = $_POST['funcionario_id'];
    $usuario_id = $_POST['usuario_id'];
    $valor = $_POST['valor'];
    $data_pagamento = $_POST['data_pagamento'];
    $tipo = $_POST['tipo'];
    $forma_pagamento = $_POST['forma_pagamento'];
    $status = $_POST['status'];
    $observacao = $_POST['observacao'];

    if (
        !empty($funcionario_id) &&
        !empty($usuario_id) &&
        !empty($valor) &&
        !empty($data_pagamento)
    ) {

        $dados = [
            "funcionario_id" => $funcionario_id,
            "usuario_id" => $usuario_id,
            "valor" => $valor,
            "data_pagamento" => $data_pagamento,
            "tipo" => $tipo,
            "forma_pagamento" => $forma_pagamento,
            "status" => $status,
            "observacao" => $observacao
        ];

        create(
            $pdo,
            "pagamentos",
            $dados
        );

        header(
            "Location: pagamentos.php"
        );

        exit();

    } else {

        $mensagem =
            "Preencha todos os campos obrigatórios.";

    }

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
    <title>Registrar Pagamento - Autopeças</title>
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
            <div class="page-title">
                <h1>Registrar Pagamento</h1>
                <p>Cadastre um novo pagamento para um funcionário.</p>

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
                    <h2>Registrar pagamento</h2>
                    <p>Preencha os dados abaixo para cadastrar o pagamento.</p>
                    
                </div>
            </div>

            <?php if (!empty($mensagem)) { ?>
                <div class="mensagem">
                    <span class="material-symbols-outlined">
                        error
                    </span>
                    <?= htmlspecialchars($mensagem) ?>
                </div>

            <?php } ?>

            <div class="form-card">
                <div class="form-title">
                    <span class="material-symbols-outlined">
                        receipt_long
                    </span>

                    <div>
                        <h3>Dados do pagamento</h3>
                        <p>Informe as informações do pagamento.</p>
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
                                <option value="">
                                    Selecione o funcionário
                                </option>

                                <?php foreach (
                                    $funcionarios
                                    as $funcionario
                                ) { ?>
                                    <option
                                        value="<?= $funcionario['id'] ?>"
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
                                <option value="">
                                    Selecione o usuário
                                </option>

                                <?php foreach (
                                    $usuarios
                                    as $usuario
                                ) { ?>
                                    <option
                                        value="<?= $usuario['id'] ?>"
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
                                min="0"
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
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label>
                                Tipo
                            </label>

                            <select name="tipo">
                                <option value="salario">
                                    Salário
                                </option>
                                <option value="bonus">
                                    Bônus
                                </option>
                                <option value="adiantamento">
                                    Adiantamento
                                </option>
                                <option value="vale">
                                    Vale
                                </option>
                                <option value="outro">
                                    Outro
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                Forma de pagamento
                            </label>

                            <select name="forma_pagamento">
                                <option value="pix">
                                    PIX
                                </option>
                                <option value="transferencia">
                                    Transferência
                                </option>
                                <option value="dinheiro">
                                    Dinheiro
                                </option>
                                <option value="outro">
                                    Outro
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                Status
                            </label>

                            <select name="status">
                                <option value="pendente">
                                    Pendente
                                </option>
                                <option value="pago">
                                    Pago
                                </option>
                                <option value="atrasado">
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
                            ></textarea>
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
                            name="cadastrar"
                            class="btn-save"
                        >
                            SALVAR PAGAMENTO
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>
</body>
</html>
