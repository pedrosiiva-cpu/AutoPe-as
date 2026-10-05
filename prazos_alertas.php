<?php
require_once __DIR__ . '/auth.php';
exigirLogin();

require_once 'init.php';

if (!isset($pdo)) {
    die('Erro na conexão com o banco de dados.');
}


/* ==============================
   SALVAR CONFIGURAÇÃO
================================ */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $diasAntecedencia = isset($_POST['dias_antecedencia'])
        ? (int) $_POST['dias_antecedencia']
        : 3;

    if ($diasAntecedencia < 1) {
        $diasAntecedencia = 1;
    }

    if ($diasAntecedencia > 30) {
        $diasAntecedencia = 30;
    }

    $stmt = $pdo->prepare("
        UPDATE configuracoes_alertas
        SET dias_antecedencia = :dias
        WHERE id = 1
    ");

    $stmt->execute([
        ':dias' => $diasAntecedencia
    ]);

    header('Location: prazos_alertas.php?salvo=1');
    exit;
}


/* ==============================
   CONFIGURAÇÃO ATUAL
================================ */

$stmt = $pdo->query("
    SELECT dias_antecedencia
    FROM configuracoes_alertas
    WHERE id = 1
");

$configuracao = $stmt->fetch(PDO::FETCH_ASSOC);

$diasAntecedencia = $configuracao
    ? (int) $configuracao['dias_antecedencia']
    : 3;


/* ==============================
   PAGAMENTOS ATRASADOS
================================ */

$stmt = $pdo->query("
    SELECT
        pp.id,
        f.nome,
        f.cargo,
        p.valor,
        pp.data_prazo,
        DATEDIFF(CURDATE(), pp.data_prazo) AS dias_atraso
    FROM prazos_pagamentos pp
    INNER JOIN funcionarios f
        ON f.id = pp.funcionario_id
    LEFT JOIN pagamentos p
        ON p.funcionario_id = pp.funcionario_id
        AND p.data_pagamento = pp.data_prazo
    WHERE pp.data_prazo < CURDATE()
      AND pp.status = 'atrasado'
    ORDER BY pp.data_prazo ASC
");

$atrasados = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ==============================
   PRÓXIMOS PAGAMENTOS
================================ */

$stmt = $pdo->prepare("
    SELECT
        pp.id,
        f.nome,
        f.cargo,
        p.valor,
        pp.data_prazo,
        DATEDIFF(pp.data_prazo, CURDATE()) AS dias_restantes
    FROM prazos_pagamentos pp
    INNER JOIN funcionarios f
        ON f.id = pp.funcionario_id
    LEFT JOIN pagamentos p
        ON p.funcionario_id = pp.funcionario_id
        AND p.data_pagamento = pp.data_prazo
    WHERE pp.data_prazo >= CURDATE()
      AND pp.data_prazo <= DATE_ADD(CURDATE(), INTERVAL :dias DAY)
      AND pp.status = 'pendente'
    ORDER BY pp.data_prazo ASC
");

$stmt->execute([
    ':dias' => $diasAntecedencia
]);

$proximos = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ==============================
   ALERTAS
================================ */

$stmt = $pdo->query("
    SELECT
        id,
        mensagem,
        data_criacao,
        visualizado
    FROM alertas
    ORDER BY data_criacao DESC
    LIMIT 5
");

$alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ==============================
   TOTAL DE ALERTAS ATIVOS
================================ */

$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM alertas
    WHERE visualizado = FALSE
");

$totalAlertas = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/* ==============================
   MÊS ATUAL
================================ */

$stmt = $pdo->query("
    SELECT COALESCE(SUM(valor), 0) AS total
    FROM pagamentos
    WHERE MONTH(data_pagamento) = MONTH(CURDATE())
      AND YEAR(data_pagamento) = YEAR(CURDATE())
");

$totalMes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/* ==============================
   CALENDÁRIO
================================ */

$mes = isset($_GET['mes']) ? (int) $_GET['mes'] : (int) date('m');
$ano = isset($_GET['ano']) ? (int) $_GET['ano'] : (int) date('Y');

if ($mes < 1) {
    $mes = 12;
    $ano--;
}

if ($mes > 12) {
    $mes = 1;
    $ano++;
}

$primeiroDia = mktime(0, 0, 0, $mes, 1, $ano);

$diasNoMes = date('t', $primeiroDia);

$diaSemana = date('w', $primeiroDia);

$meses = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

$nomeMes = $meses[$mes];


/* ==============================
   DATAS COM PAGAMENTOS
================================ */

$primeiraData = sprintf(
    '%04d-%02d-01',
    $ano,
    $mes
);

$ultimaData = sprintf(
    '%04d-%02d-%02d',
    $ano,
    $mes,
    $diasNoMes
);

$stmt = $pdo->prepare("
    SELECT
        data_prazo,
        status
    FROM prazos_pagamentos
    WHERE data_prazo BETWEEN :inicio AND :fim
");

$stmt->execute([
    ':inicio' => $primeiraData,
    ':fim' => $ultimaData
]);

$diasComPrazo = [];

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $prazo) {

    $dia = (int) date(
        'j',
        strtotime($prazo['data_prazo'])
    );

    $diasComPrazo[$dia] = $prazo['status'];
}


/* ==============================
   MÊS ANTERIOR E PRÓXIMO
================================ */

$mesAnterior = $mes - 1;
$anoAnterior = $ano;

if ($mesAnterior < 1) {
    $mesAnterior = 12;
    $anoAnterior--;
}

$mesProximo = $mes + 1;
$anoProximo = $ano;

if ($mesProximo > 12) {
    $mesProximo = 1;
    $anoProximo++;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Prazos e Alertas</title>

    <link rel="stylesheet" href="css/prazos_alertas.css">

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
    >

<link rel="stylesheet" href="css/sidebar.css">
</head>

<body>

<div class="app">


    <!-- SIDEBAR -->

    <?php require __DIR__ . '/sidebar.php'; ?>


    <!-- MAIN -->

    <main class="main">


        <!-- TOPO -->

        <header class="topbar">

            <div class="menu-icon app-sidebar-toggle">

                <span class="material-symbols-outlined">
                    menu
                </span>

            </div>


            <div class="page-title">

                <h1>
                    Prazos e Alertas
                </h1>

                <p>
                    Acompanhe os prazos de pagamento e configure seus alertas.
                </p>

            </div>


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

                <span class="material-symbols-outlined expand">
                    expand_more
                </span>

            </div>

        </header>


        <!-- CONTEÚDO -->

        <section class="content">


            <?php if (isset($_GET['salvo'])): ?>

                <div class="success-message">

                    <span class="material-symbols-outlined">
                        check_circle
                    </span>

                    Configuração salva com sucesso!

                </div>

            <?php endif; ?>


            <!-- CARDS -->

            <div class="summary-cards">


                <div class="summary-card danger-card">

                    <div class="summary-icon">

                        <span class="material-symbols-outlined">
                            priority_high
                        </span>

                    </div>

                    <div>

                        <div class="summary-title">
                            Pagamentos atrasados
                        </div>

                        <div class="summary-number">
                            <?= count($atrasados) ?>
                        </div>

                        <div class="summary-text">
                            pagamento em atraso
                        </div>

                    </div>

                    <span class="material-symbols-outlined arrow">
                        chevron_right
                    </span>

                </div>


                <div class="summary-card warning-card">

                    <div class="summary-icon">

                        <span class="material-symbols-outlined">
                            schedule
                        </span>

                    </div>

                    <div>

                        <div class="summary-title">
                            Próximos pagamentos
                        </div>

                        <div class="summary-number">
                            <?= count($proximos) ?>
                        </div>

                        <div class="summary-text">
                            nos próximos <?= $diasAntecedencia ?> dias
                        </div>

                    </div>

                    <span class="material-symbols-outlined arrow">
                        chevron_right
                    </span>

                </div>


                <div class="summary-card neutral-card">

                    <div class="summary-icon">

                        <span class="material-symbols-outlined">
                            notifications
                        </span>

                    </div>

                    <div>

                        <div class="summary-title">
                            Alertas ativos
                        </div>

                        <div class="summary-number">
                            <?= $totalAlertas ?>
                        </div>

                        <div class="summary-text">
                            configurados
                        </div>

                    </div>

                    <span class="material-symbols-outlined arrow">
                        chevron_right
                    </span>

                </div>

            </div>


            <div class="main-grid">


                <!-- LADO ESQUERDO -->

                <div class="left-area">


                    <!-- ATRASADOS -->

                    <section class="panel">

                        <div class="panel-header danger-header">

                            <div class="panel-title">

                                <div class="panel-icon danger-icon">

                                    <span class="material-symbols-outlined">
                                        priority_high
                                    </span>

                                </div>

                                <h2>
                                    Pagamentos atrasados
                                </h2>

                            </div>


                            <span class="quantity danger-quantity">

                                <?= count($atrasados) ?>

                                pendente

                            </span>

                        </div>


                        <div class="table-wrapper">

                            <table>

                                <thead>

                                    <tr>

                                        <th>Funcionário</th>

                                        <th>Cargo</th>

                                        <th>Valor</th>

                                        <th>Vencimento</th>

                                        <th>Situação</th>

                                    </tr>

                                </thead>


                                <tbody>

                                <?php foreach ($atrasados as $item): ?>

                                    <tr>

                                        <td class="employee-name">

                                            <?= htmlspecialchars($item['nome']) ?>

                                        </td>

                                        <td>

                                            <?= htmlspecialchars($item['cargo']) ?>

                                        </td>

                                        <td>

                                            R$

                                            <?= number_format(
                                                $item['valor'] ?? 0,
                                                2,
                                                ',',
                                                '.'
                                            ) ?>

                                        </td>

                                        <td>

                                            <?= date(
                                                'd/m/Y',
                                                strtotime($item['data_prazo'])
                                            ) ?>

                                        </td>

                                        <td>

                                            <span class="late-badge">

                                                Atrasado há

                                                <?= max(
                                                    1,
                                                    $item['dias_atraso']
                                                ) ?>

                                                dia<?= $item['dias_atraso'] > 1 ? 's' : '' ?>

                                            </span>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>


                                <?php if (!$atrasados): ?>

                                    <tr>

                                        <td colspan="5" class="empty">
                                            Nenhum pagamento atrasado.
                                        </td>

                                    </tr>

                                <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </section>


                    <!-- PRÓXIMOS -->

                    <section class="panel">

                        <div class="panel-header warning-header">

                            <div class="panel-title">

                                <div class="panel-icon warning-icon">

                                    <span class="material-symbols-outlined">
                                        schedule
                                    </span>

                                </div>

                                <h2>
                                    Próximos pagamentos
                                </h2>

                            </div>


                            <span class="quantity warning-quantity">

                                <?= count($proximos) ?>

                                próximos

                            </span>

                        </div>


                        <div class="table-wrapper">

                            <table>

                                <thead>

                                    <tr>

                                        <th>Funcionário</th>

                                        <th>Cargo</th>

                                        <th>Valor</th>

                                        <th>Vencimento</th>

                                        <th>Dias restantes</th>

                                    </tr>

                                </thead>


                                <tbody>

                                <?php foreach ($proximos as $item): ?>

                                    <tr>

                                        <td class="employee-name">

                                            <?= htmlspecialchars($item['nome']) ?>

                                        </td>

                                        <td>

                                            <?= htmlspecialchars($item['cargo']) ?>

                                        </td>

                                        <td>

                                            R$

                                            <?= number_format(
                                                $item['valor'] ?? 0,
                                                2,
                                                ',',
                                                '.'
                                            ) ?>

                                        </td>

                                        <td>

                                            <?= date(
                                                'd/m/Y',
                                                strtotime($item['data_prazo'])
                                            ) ?>

                                        </td>

                                        <td>

                                            <span class="remaining-badge">

                                                <?= $item['dias_restantes'] ?>

                                                dias

                                            </span>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>


                                <?php if (!$proximos): ?>

                                    <tr>

                                        <td colspan="5" class="empty">
                                            Nenhum pagamento próximo.
                                        </td>

                                    </tr>

                                <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </section>


                    <!-- ALERTAS -->

                    <section class="panel alerts-panel">

                        <div class="alerts-title">

                            <div class="panel-icon neutral-icon">

                                <span class="material-symbols-outlined">
                                    notifications
                                </span>

                            </div>

                            <h2>
                                Últimos alertas
                            </h2>

                            <span class="alerts-count">
                                <?= count($alertas) ?>
                            </span>

                        </div>


                        <div class="alerts-list">

                        <?php foreach ($alertas as $alerta): ?>

                            <div class="alert-item">

                                <div class="alert-type-icon">

                                    <span class="material-symbols-outlined">

                                        <?= $alerta['visualizado']
                                            ? 'notifications'
                                            : 'error' ?>

                                    </span>

                                </div>


                                <div class="alert-message">

                                    <?= htmlspecialchars(
                                        $alerta['mensagem']
                                    ) ?>

                                </div>


                                <div class="alert-date">

                                    <?= date(
                                        'd/m/Y',
                                        strtotime($alerta['data_criacao'])
                                    ) ?>

                                </div>

                            </div>

                        <?php endforeach; ?>


                        <?php if (!$alertas): ?>

                            <div class="empty-alerts">
                                Nenhum alerta encontrado.
                            </div>

                        <?php endif; ?>

                        </div>

                    </section>

                </div>


                <!-- LADO DIREITO -->

                <div class="right-area">


                    <!-- CONFIGURAÇÃO -->

                    <section class="settings-panel">

                        <div class="right-title">

                            <span class="material-symbols-outlined">
                                settings
                            </span>

                            <h2>
                                Configuração de alertas
                            </h2>

                        </div>


                        <p class="right-description">

                            Defina quantos dias antes do vencimento deseja
                            receber os alertas.

                        </p>


                        <form method="POST">

                            <div class="setting-box">

                                <span class="setting-label">
                                    Exibir alerta
                                </span>


                                <input
                                    type="number"
                                    name="dias_antecedencia"
                                    value="<?= $diasAntecedencia ?>"
                                    min="1"
                                    max="30"
                                    required
                                >


                                <span class="setting-days">
                                    dias antes do vencimento.
                                </span>

                            </div>


                            <button
                                type="submit"
                                class="save-button"
                            >

                                <span class="material-symbols-outlined">
                                    save
                                </span>

                                SALVAR CONFIGURAÇÕES

                            </button>

                        </form>

                    </section>


                    <!-- CALENDÁRIO -->

                    <section class="calendar-panel">

                        <div class="calendar-title">

                            <span class="material-symbols-outlined">
                                calendar_month
                            </span>

                            <h2>
                                Calendário de pagamentos
                            </h2>

                        </div>


                        <div class="calendar-navigation">


                            <a
                                href="prazos_alertas.php?mes=<?= $mesAnterior ?>&ano=<?= $anoAnterior ?>"
                            >

                                <span class="material-symbols-outlined">
                                    chevron_left
                                </span>

                            </a>


                            <strong>

                                <?= $nomeMes ?>

                                <?= $ano ?>

                            </strong>


                            <a
                                href="prazos_alertas.php?mes=<?= $mesProximo ?>&ano=<?= $anoProximo ?>"
                            >

                                <span class="material-symbols-outlined">
                                    chevron_right
                                </span>

                            </a>

                        </div>


                        <div class="calendar-week">

                            <span>D</span>
                            <span>S</span>
                            <span>T</span>
                            <span>Q</span>
                            <span>Q</span>
                            <span>S</span>
                            <span>S</span>

                        </div>


                        <div class="calendar-days">

                        <?php

                        for ($i = 0; $i < $diaSemana; $i++) {

                            echo '<span class="empty-day"></span>';

                        }


                        for ($dia = 1; $dia <= $diasNoMes; $dia++) {

                            $classe = '';

                            if (
                                $dia == date('j') &&
                                $mes == date('m') &&
                                $ano == date('Y')
                            ) {

                                $classe = 'today';

                            }


                            if (
                                isset($diasComPrazo[$dia]) &&
                                $diasComPrazo[$dia] === 'atrasado'
                            ) {

                                $classe = 'overdue-day';

                            }


                            echo '<span class="' .
                                $classe .
                                '">' .
                                $dia .
                                '</span>';

                        }

                        ?>

                        </div>


                        <?php if ($atrasados): ?>

                            <div class="calendar-warning">

                                <span class="material-symbols-outlined">
                                    info
                                </span>

                                <div>

                                    <strong>

                                        O dia

                                        <?= date(
                                            'd/m/Y',
                                            strtotime(
                                                $atrasados[0]['data_prazo']
                                            )
                                        ) ?>

                                        possui um pagamento em atraso.

                                    </strong>


                                    <p>
                                        Verifique a situação no quadro ao lado.
                                    </p>

                                </div>

                            </div>

                        <?php endif; ?>

                    </section>

                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>
