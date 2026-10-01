<?php

require_once 'auth.php';
exigirLogin();
require_once 'init.php';

$hoje = date('Y-m-d');
$fim = date('Y-m-d', strtotime('+7 days'));

$consulta = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'ativo') AS ativos,
        SUM(status = 'inativo') AS inativos
    FROM funcionarios
");

$funcionarios = $consulta->fetch(PDO::FETCH_ASSOC);

$inicioMes = date('Y-m-01');
$fimMes = date('Y-m-t');

$consulta = $pdo->query("
    SELECT COALESCE(SUM(valor), 0) AS total
    FROM pagamentos
    WHERE status = 'pago'
    AND data_pagamento BETWEEN '$inicioMes' AND '$fimMes'
");

$pagos = $consulta->fetch(PDO::FETCH_ASSOC);

$consulta = $pdo->query("
    SELECT COALESCE(SUM(valor), 0) AS total
    FROM pagamentos
    WHERE status = 'pendente'
    AND data_pagamento BETWEEN '$inicioMes' AND '$fimMes'
");

$pendentes = $consulta->fetch(PDO::FETCH_ASSOC);

$consulta = $pdo->query("
    SELECT COUNT(*) AS total
    FROM prazos_pagamentos
    WHERE status = 'pendente'
    AND data_prazo BETWEEN '$hoje' AND '$fim'
");

$proximos = $consulta->fetch(PDO::FETCH_ASSOC);

$consulta = $pdo->query("
    SELECT
        p.id,
        p.valor,
        p.data_pagamento,
        p.status,
        f.nome,
        f.cargo
    FROM pagamentos p
    INNER JOIN funcionarios f
        ON f.id = p.funcionario_id
    ORDER BY p.data_pagamento DESC, p.id DESC
    LIMIT 5
");

$pagamentos = $consulta->fetchAll(PDO::FETCH_ASSOC);

$consulta = $pdo->query("
    SELECT
        pp.id,
        pp.data_prazo,
        pp.descricao,
        f.nome,
        f.cargo,
        DATEDIFF('$hoje', pp.data_prazo) AS dias_atrasado
    FROM prazos_pagamentos pp
    INNER JOIN funcionarios f
        ON f.id = pp.funcionario_id
    WHERE pp.status = 'atrasado'
    OR (
        pp.status = 'pendente'
        AND pp.data_prazo < '$hoje'
    )
    ORDER BY pp.data_prazo ASC
");

$atrasados = $consulta->fetchAll(PDO::FETCH_ASSOC);

$consulta = $pdo->query("
    SELECT
        pp.id,
        pp.data_prazo,
        pp.descricao,
        f.nome,
        f.cargo,
        DATEDIFF(pp.data_prazo, '$hoje') AS dias
    FROM prazos_pagamentos pp
    INNER JOIN funcionarios f
        ON f.id = pp.funcionario_id
    WHERE pp.status = 'pendente'
    AND pp.data_prazo BETWEEN '$hoje' AND '$fim'
    ORDER BY pp.data_prazo ASC
    LIMIT 5
");

$proximosPrazos = $consulta->fetchAll(PDO::FETCH_ASSOC);

$consulta = $pdo->query("
    SELECT COUNT(*) AS total
    FROM alertas
    WHERE visualizado = FALSE
");

$notificacoes = $consulta->fetch(PDO::FETCH_ASSOC);

$consulta = $pdo->query("
    SELECT nome, perfil
    FROM usuarios
    WHERE ativo = TRUE
    ORDER BY id ASC
    LIMIT 1
");

$usuario = $consulta->fetch(PDO::FETCH_ASSOC);

function dinheiro($valor)
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

function dataBrasil($data)
{
    return date('d/m/Y', strtotime($data));
}

function iniciais($nome)
{
    $partes = explode(' ', trim($nome));

    if (count($partes) == 1) {
        return strtoupper(substr($partes[0], 0, 2));
    }

    return strtoupper(
        substr($partes[0], 0, 1) .
        substr($partes[count($partes) - 1], 0, 1)
    );
}

function escapar($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function textoStatus($status)
{
    if ($status === 'pago') {
        return 'Pago';
    }

    if ($status === 'pendente') {
        return 'Pendente';
    }

    if ($status === 'atrasado') {
        return 'Atrasado';
    }

    return ucfirst($status);
}

function textoDias($dias)
{
    $dias = (int) $dias;

    if ($dias <= 0) {
        return 'Vence hoje';
    }

    if ($dias == 1) {
        return 'Vence em um dia';
    }

    return 'Vence em ' . $dias . ' dias';
}

function textoAtraso($dias)
{
    $dias = (int) $dias;

    if ($dias <= 1) {
        return 'Atrasado há um dia';
    }

    return 'Atrasado há ' . $dias . ' dias';
}

$totalPago = (float) $pagos['total'];
$totalPendente = (float) $pendentes['total'];

$totalGeral = $totalPago + $totalPendente;

if ($totalGeral > 0) {
    $porcentagemPago = ($totalPago / $totalGeral) * 100;
    $porcentagemPendente = ($totalPendente / $totalGeral) * 100;
} else {
    $porcentagemPago = 0;
    $porcentagemPendente = 0;
}

$anguloPago = $porcentagemPago * 3.6;

$semanasGrafico = [];

$dia = 1;
$ultimoDia = date('t');

while ($dia <= $ultimoDia) {

    $inicioSemana = $dia;
    $fimSemana = min($dia + 6, $ultimoDia);

    $dataInicio = date(
        'Y-m-' . str_pad($inicioSemana, 2, '0', STR_PAD_LEFT)
    );

    $dataFim = date(
        'Y-m-' . str_pad($fimSemana, 2, '0', STR_PAD_LEFT)
    );

    $consulta = $pdo->query("
        SELECT COALESCE(SUM(valor), 0) AS total
        FROM pagamentos
        WHERE status = 'pago'
        AND data_pagamento BETWEEN '$dataInicio' AND '$dataFim'
    ");

    $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

    $valor = (float) $resultado['total'];

    $semanasGrafico[] = [
        'inicio' => $inicioSemana,
        'fim' => $fimSemana,
        'valor' => $valor
    ];

    $dia += 7;
}

$maiorValor = 0;

foreach ($semanasGrafico as $semana) {
    if ($semana['valor'] > $maiorValor) {
        $maiorValor = $semana['valor'];
    }
}

$quantidadeAtrasados = count($atrasados);
$quantidadeProximos = count($proximosPrazos);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>Dashboard</title>
</head>

<body>
<div class="flex">
    <aside class="sidebar">
            <div class="brand">
                <div class="brand-icon"></div>
                <div class="brand-name">AUTOPEÇAS</div>
                <div class="brand-sub">GESTÃO DE PAGAMENTOS</div>
            </div>
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-link active">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9.5 12 3l9 6.5" />
                        <path d="M5 9.5V21h14V9.5" />
                        <path d="M9 21v-6h6v6" />
                    </svg>
                    Dashboard
                </a>
                <a href="funcionarios.php" class="nav-link">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="7" r="4" />
                        <path d="M2 21v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1" />
                        <path d="M16.5 3.2a4 4 0 0 1 0 7.6" />
                        <path d="M22 21v-1a5 5 0 0 0-3.5-4.8" />
                    </svg>
                    Funcionários
                </a>
                <a href="relatorio.php" class="nav-link">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="5" width="20" height="14" rx="2.5" />
                        <line x1="2" y1="10" x2="22" y2="10" />
                    </svg>
                    Pagamentos
                </a>
                <a href="relatorio.php" class="nav-link">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="6" y1="20" x2="6" y2="12" />
                        <line x1="12" y1="20" x2="12" y2="5" />
                        <line x1="18" y1="20" x2="18" y2="15" />
                    </svg>
                    Relatórios
                </a>
            </nav>
            <div class="nav-footer">
                <a href="logout.php" class="nav-link logout">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                    Sair
                </a>
            </div>
    </aside>

    <main class="conteudo">

        <header class="cabecalho">

            <div class="titulo">

                <div>
                    <h2>Dashboard</h2>
                    <p>Visão geral do sistema</p>
                </div>

            </div>

            <div class="usuario">

                <div class="dados-usuario">

                    <div class="icone-usuario">
                        <i class="bi bi-person-circle"></i>
                    </div>

                    <div>
                        <strong>
                            <?= escapar($usuario['nome'] ?? 'Gestor Financeiro') ?>
                        </strong>

                        <span>
                            <?= isset($usuario['perfil']) && $usuario['perfil'] === 'gestor_financeiro' ? 'Gestor Financeiro' : 'Gerente' ?>
                        </span>
                    </div>

                    <span class="seta">
                        <i class="bi bi-arrow-down-short"></i>
                    </span>

                </div>

            </div>

        </header>

        <section class="painel">

            <section class="cartoes">

                <article class="card">

                    <div class="icone-card">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <div class="dados-card">

                        <span class="titulo-card">
                            Funcionários Cadastrados
                        </span>

                        <strong class="valor-card">
                            <?= (int) $funcionarios['total'] ?>
                        </strong>

                        <div class="texto-card">
                            Ativos: <?= (int) $funcionarios['ativos'] ?>

                            <span>
                                <i class="bi bi-dot"></i>
                            </span>

                            Inativos: <?= (int) $funcionarios['inativos'] ?>
                        </div>

                    </div>

                </article>

                <article class="card">

                    <div class="icone-card">
                        <i class="bi bi-wallet2"></i>
                    </div>

                    <div class="dados-card">

                        <span class="titulo-card">
                            Pagamentos Realizados
                        </span>

                        <strong class="valor-card">
                            <?= dinheiro($totalPago) ?>
                        </strong>

                        <div class="texto-card">
                            Este mês
                        </div>

                    </div>

                </article>

                <article class="card">

                    <div class="icone-card">
                        <i class="bi bi-clock-fill"></i>
                    </div>

                    <div class="dados-card">

                        <span class="titulo-card">
                            Pagamentos Pendentes
                        </span>

                        <strong class="valor-card">
                            <?= dinheiro($totalPendente) ?>
                        </strong>

                        <div class="texto-card">
                            Este mês
                        </div>

                    </div>

                </article>

                <article class="card">

                    <div class="icone-card">
                        <i class="bi bi-calendar3"></i>
                    </div>

                    <div class="dados-card">

                        <span class="titulo-card">
                            Próximos Pagamentos
                        </span>

                        <strong class="valor-card">
                            <?= str_pad((int) $proximos['total'], 2, '0', STR_PAD_LEFT) ?>
                        </strong>

                        <div class="texto-card">
                            Nos próximos 7 dias
                        </div>

                    </div>

                </article>

            </section>

            <section class="parte-meio">

                <article class="caixa pagamentos-recentes">

                    <div class="cabecalho-box">
                        <h3>Pagamentos Recentes</h3>
                        <a href="#" class="botao-ver">Ver Todos</a>
                    </div>

                    <div class="area-tabela">

                        <table class="tabela">

                            <thead>

                                <tr>
                                    <th>Funcionários</th>
                                    <th>Valor</th>
                                    <th>Data do Pagamento</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($pagamentos as $pagamento): ?>

                                    <tr>

                                        <td>

                                            <div class="funcionario">

                                                <div class="foto">
                                                    <?= escapar(iniciais($pagamento['nome'])) ?>
                                                </div>

                                                <div class="dados-funcionario">

                                                    <strong>
                                                        <?= escapar($pagamento['nome']) ?>
                                                    </strong>

                                                    <span>
                                                        <?= escapar($pagamento['cargo']) ?>
                                                    </span>

                                                </div>

                                            </div>

                                        </td>

                                        <td>
                                            <?= dinheiro($pagamento['valor']) ?>
                                        </td>

                                        <td>
                                            <?= dataBrasil($pagamento['data_pagamento']) ?>
                                        </td>

                                        <td>

                                            <span class="situacao <?= escapar($pagamento['status']) ?>">
                                                <?= textoStatus($pagamento['status']) ?>
                                            </span>

                                        </td>

                                        <td>
                                            <a href="#" class="botao-tres">:</a>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                                <?php if (count($pagamentos) === 0): ?>

                                    <tr>

                                        <td colspan="5">
                                            Nenhum pagamento encontrado.
                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </article>

                <article class="caixa alertas">

                    <div class="cabecalho-box">

                        <h3>
                            Próximos prazos e alertas
                        </h3>

                        <a href="#" class="botao-ver">
                            Ver Todos
                        </a>

                    </div>

                    <div class="bloco-atrasado">

                        <div class="titulo-alerta">

                            <div class="icone-alerta">
                                <span>!</span>
                                <strong>Pagamento(s) atrasados</strong>
                            </div>

                            <span class="quantidade">
                                <?= $quantidadeAtrasados ?>
                            </span>

                        </div>

                        <?php foreach ($atrasados as $atrasado): ?>

                            <div class="item-alerta">

                                <div>

                                    <strong>
                                        <?= escapar($atrasado['nome']) ?> - <?= escapar($atrasado['cargo']) ?>
                                    </strong>

                                    <span>
                                        Vencimento: <?= dataBrasil($atrasado['data_prazo']) ?>
                                    </span>

                                </div>

                                <strong>
                                    <?= textoAtraso($atrasado['dias_atrasado']) ?>
                                </strong>

                            </div>

                        <?php endforeach; ?>

                        <?php if ($quantidadeAtrasados === 0): ?>

                            <div class="item-alerta">

                                <div>
                                    <strong>
                                        Nenhum pagamento atrasado
                                    </strong>
                                </div>

                            </div>

                        <?php endif; ?>

                    </div>

                    <div class="bloco-proximo">

                        <div class="titulo-alerta">

                            <div class="icone-alerta">
                                <span>!</span>
                                <strong>Próximos pagamentos</strong>
                            </div>

                            <span class="quantidade">
                                <?= $quantidadeProximos ?>
                            </span>

                        </div>

                        <?php foreach ($proximosPrazos as $prazo): ?>

                            <div class="item-alerta">

                                <div>

                                    <strong>
                                        <?= escapar($prazo['nome']) ?> - <?= escapar($prazo['cargo']) ?>
                                    </strong>

                                    <span>
                                        Vencimento: <?= dataBrasil($prazo['data_prazo']) ?>
                                    </span>

                                </div>

                                <strong>
                                    <?= textoDias($prazo['dias']) ?>
                                </strong>

                            </div>

                        <?php endforeach; ?>

                        <?php if ($quantidadeProximos === 0): ?>

                            <div class="item-alerta">

                                <div>
                                    <strong>
                                        Nenhum pagamento próximo
                                    </strong>
                                </div>

                            </div>

                        <?php endif; ?>

                    </div>

                </article>

            </section>

            <section class="box resumo-financeiro">

                <div class="cabecalho-box">

                    <h3>
                        Resumo financeiro (Este mês)
                    </h3>

                </div>

                <div class="resumo">

                    <div
                        class="grafico-redondo"
                        style="background: conic-gradient(
                            #b51e2b 0deg <?= $anguloPago ?>deg,
                            #e6a16c <?= $anguloPago ?>deg 360deg
                        );"
                    >

                        <div class="valor-total">

                            <strong>
                                <?= dinheiro($totalGeral) ?>
                            </strong>

                            <span>
                                Total geral
                            </span>

                        </div>

                    </div>

                    <div class="legenda">

                        <div class="item-legenda">

                            <span class="bolinha vermelha">
                                <i class="bi bi-dot"></i>
                            </span>

                            <div>

                                <span>
                                    Pagamentos realizados
                                </span>

                                <strong>
                                    <?= dinheiro($totalPago) ?>
                                </strong>

                                <small>
                                    <?= number_format($porcentagemPago, 1, ',', '.') ?>%
                                </small>

                            </div>

                        </div>

                    </div>

                    <div class="item-legenda">

                        <span class="bolinha laranja">
                            <i class="bi bi-dot"></i>
                        </span>

                        <div>

                            <span>
                                Pagamentos pendentes
                            </span>

                            <strong>
                                <?= dinheiro($totalPendente) ?>
                            </strong>

                            <small>
                                <?= number_format($porcentagemPendente, 1, ',', '.') ?>%
                            </small>

                        </div>

                    </div>

                </div>

                <div class="grafico-barras">

                    <div class="valores-grafico">

                        <span>
                            <?= dinheiro($maiorValor) ?>
                        </span>

                        <span>
                            <?= dinheiro($maiorValor * 0.75) ?>
                        </span>

                        <span>
                            <?= dinheiro($maiorValor * 0.50) ?>
                        </span>

                        <span>
                            <?= dinheiro($maiorValor * 0.25) ?>
                        </span>

                        <span>
                            R$ 0
                        </span>

                    </div>

                    <div class="barras">

                        <?php foreach ($semanasGrafico as $semana): ?>

                            <?php

                            if ($maiorValor > 0) {
                                $altura = ($semana['valor'] / $maiorValor) * 100;

                                if ($altura < 2 && $semana['valor'] > 0) {
                                    $altura = 2;
                                }
                            } else {
                                $altura = 0;
                            }

                            ?>

                            <div class="barra-item">

                                <div
                                    class="barra"
                                    style="height: <?= $altura ?>%;"
                                    title="<?= dinheiro($semana['valor']) ?>"
                                ></div>

                                <span>

                                    <?= str_pad($semana['inicio'], 2, '0', STR_PAD_LEFT) ?>

                                    a

                                    <?= str_pad($semana['fim'], 2, '0', STR_PAD_LEFT) ?>

                                    <br>

                                    <?= date('M', strtotime($inicioMes)) ?>

                                </span>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>

            </section>

        </section>

    </main>
</div>
    
</body>
</html>
