<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
exigirLogin();
require_once __DIR__ . '/init.php';

function escaparRelatorio($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function dataValida(string $data): bool
{
    $objeto = DateTime::createFromFormat('!Y-m-d', $data);
    return $objeto instanceof DateTime && $objeto->format('Y-m-d') === $data;
}

$hoje = new DateTimeImmutable('today');
$inicioPadrao = $hoje->modify('first day of this month')->modify('-5 months');
$inicio = trim((string) ($_GET['data_inicio'] ?? $inicioPadrao->format('Y-m-d')));
$fim = trim((string) ($_GET['data_fim'] ?? $hoje->format('Y-m-d')));
if (!dataValida($inicio)) $inicio = $inicioPadrao->format('Y-m-d');
if (!dataValida($fim)) $fim = $hoje->format('Y-m-d');
if ($inicio > $fim) [$inicio, $fim] = [$fim, $inicio];

$funcionarioId = filter_var($_GET['funcionario_id'] ?? '', FILTER_VALIDATE_INT);
$situacao = (string) ($_GET['situacao'] ?? 'todos');
if (!in_array($situacao, ['todos', 'pago', 'pendente'], true)) $situacao = 'todos';
$verTodos = ($_GET['ver_todos'] ?? '') === '1';
$exportar = ($_GET['exportar'] ?? '') === 'csv';

$listaFuncionarios = $pdo->query('SELECT id, nome FROM funcionarios ORDER BY nome')->fetchAll();
$idsFuncionarios = array_map('intval', array_column($listaFuncionarios, 'id'));
if ($funcionarioId === false || !in_array((int) $funcionarioId, $idsFuncionarios, true)) $funcionarioId = null;
else $funcionarioId = (int) $funcionarioId;

$condicoes = ['p.data_pagamento BETWEEN :data_inicio AND :data_fim'];
$parametros = ['data_inicio' => $inicio, 'data_fim' => $fim];
if ($funcionarioId !== null) {
    $condicoes[] = 'p.funcionario_id = :funcionario_id';
    $parametros['funcionario_id'] = $funcionarioId;
}
if ($situacao !== 'todos') {
    $condicoes[] = 'p.status = :situacao';
    $parametros['situacao'] = $situacao;
}
$where = implode(' AND ', $condicoes);
$stmt = $pdo->prepare("SELECT
    COALESCE(SUM(CASE WHEN p.status = 'pago' THEN p.valor ELSE 0 END), 0) AS pago,
    COALESCE(SUM(CASE WHEN p.status = 'pendente' THEN p.valor ELSE 0 END), 0) AS pendente,
    COUNT(DISTINCT CASE WHEN p.status IN ('pago','pendente') THEN p.funcionario_id END) AS funcionarios,
    COUNT(*) AS quantidade
    FROM pagamentos p WHERE {$where}");
$stmt->execute($parametros);
$resumo = $stmt->fetch();
$totalPago = (float) $resumo['pago'];
$totalPendente = (float) $resumo['pendente'];
$somaTotal = $totalPago + $totalPendente;
$percentualPago = $somaTotal > 0 ? $totalPago / $somaTotal * 100 : 0;
$percentualPendente = $somaTotal > 0 ? 100 - $percentualPago : 0;

// Preenche também meses sem pagamentos para manter a escala do gráfico estável.
$meses = [];
$cursor = (new DateTimeImmutable($inicio))->modify('first day of this month');
$ultimoMes = (new DateTimeImmutable($fim))->modify('first day of this month');
while ($cursor <= $ultimoMes && count($meses) < 36) {
    $chave = $cursor->format('Y-m');
    $meses[$chave] = ['label' => $cursor->format('m/Y'), 'pago' => 0.0, 'pendente' => 0.0];
    $cursor = $cursor->modify('+1 month');
}
$stmt = $pdo->prepare("SELECT DATE_FORMAT(p.data_pagamento, '%Y-%m') AS mes,
    SUM(CASE WHEN p.status = 'pago' THEN p.valor ELSE 0 END) AS pago,
    SUM(CASE WHEN p.status = 'pendente' THEN p.valor ELSE 0 END) AS pendente
    FROM pagamentos p WHERE {$where} GROUP BY DATE_FORMAT(p.data_pagamento, '%Y-%m') ORDER BY mes");
$stmt->execute($parametros);
foreach ($stmt->fetchAll() as $linha) {
    if (isset($meses[$linha['mes']])) {
        $meses[$linha['mes']]['pago'] = (float) $linha['pago'];
        $meses[$linha['mes']]['pendente'] = (float) $linha['pendente'];
    }
}
$maiorValorGrafico = 1.0;
foreach ($meses as $mes) $maiorValorGrafico = max($maiorValorGrafico, $mes['pago'], $mes['pendente']);

$sqlMaiores = "SELECT f.nome, f.cargo, p.valor, p.data_pagamento AS data
    FROM pagamentos p JOIN funcionarios f ON f.id = p.funcionario_id
    WHERE {$where} AND p.status = 'pago' ORDER BY p.valor DESC, p.data_pagamento DESC, p.id DESC" . ($verTodos ? '' : ' LIMIT 5');
$stmt = $pdo->prepare($sqlMaiores);
$stmt->execute($parametros);
$maioresPagamentos = $stmt->fetchAll();
$sqlPendentes = "SELECT f.nome, f.cargo, p.valor, p.data_pagamento AS data_prevista,
    GREATEST(DATEDIFF(CURDATE(), p.data_pagamento), 0) AS dias_atraso
    FROM pagamentos p JOIN funcionarios f ON f.id = p.funcionario_id
    WHERE {$where} AND p.status = 'pendente' ORDER BY p.data_pagamento ASC, p.id ASC" . ($verTodos ? '' : ' LIMIT 5');
$stmt = $pdo->prepare($sqlPendentes);
$stmt->execute($parametros);
$pendentes = $stmt->fetchAll();

if ($exportar) {
    $stmt = $pdo->prepare("SELECT f.nome, f.cargo, p.valor, p.status, p.data_pagamento
        FROM pagamentos p JOIN funcionarios f ON f.id = p.funcionario_id
        WHERE {$where} ORDER BY p.data_pagamento DESC, p.id DESC");
    $stmt->execute($parametros);
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="relatorio-pagamentos-' . $inicio . '-' . $fim . '.csv"');
    $saida = fopen('php://output', 'w');
    fwrite($saida, "\xEF\xBB\xBF");
    fputcsv($saida, ['Funcionário', 'Cargo', 'Valor', 'Situação', 'Data prevista/pagamento'], ';');
    foreach ($stmt->fetchAll() as $linha) {
        fputcsv($saida, [$linha['nome'], $linha['cargo'], number_format((float) $linha['valor'], 2, ',', '.'), $linha['status'], $linha['data_pagamento']], ';');
    }
    fclose($saida);
    exit;
}
$queryFiltros = ['data_inicio' => $inicio, 'data_fim' => $fim, 'situacao' => $situacao];
if ($funcionarioId !== null) $queryFiltros['funcionario_id'] = $funcionarioId;
$urlVerTodos = 'relatorio.php?' . http_build_query($queryFiltros + ['ver_todos' => $verTodos ? '0' : '1']);
$urlExportar = 'relatorio.php?' . http_build_query($queryFiltros + ['exportar' => 'csv']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoPeças - Relatórios</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="relatorio.css">
</head>
<body>
<div class="painel dashboard-container">
    <aside class="menu-lateral sidebar">
        <div class="menu-topo sidebar-header"><a href="dashboard.php" class="link-logo"><img src="./imagens/remove.png" alt="AutoPeças" class="logo logo-img"></a></div>
        <nav class="navegacao sidebar-nav">
            <a href="dashboard.php" class="link nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="funcionarios.php" class="link nav-link"><i class="fa-solid fa-users"></i> Funcionários</a>
            <a href="relatorio.php" class="link nav-link"><i class="fa-solid fa-dollar-sign"></i> Pagamentos</a>
            <a href="dashboard.php" class="link nav-link"><i class="fa-regular fa-bell"></i> Prazos e alertas</a>
            <a href="relatorio.php" class="link nav-link ativo active"><i class="fa-solid fa-chart-column"></i> Relatórios</a>
        </nav>
        <div class="menu-rodape sidebar-footer"><a href="logout.php" class="link nav-link texto-vermelho text-red"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sair</a></div>
    </aside>
    <main class="conteudo main-content">
        <header class="cabecalho topbar">
            <div class="cabecalho-esq topbar-left"><button class="btn-menu menu-btn" type="button" aria-label="Menu"><i class="fa-solid fa-bars"></i></button><div><h1>Relatórios</h1><p class="subtitulo subtitle">Análises financeiras por período e funcionário.</p></div></div>
        </header>
        <form method="get" action="relatorio.php" class="filtros filters-section">
            <div class="grupo-filtro filter-group"><label for="data_inicio">Data inicial</label><input type="date" id="data_inicio" name="data_inicio" value="<?= escaparRelatorio($inicio) ?>" required></div>
            <div class="grupo-filtro filter-group"><label for="data_fim">Data final</label><input type="date" id="data_fim" name="data_fim" value="<?= escaparRelatorio($fim) ?>" required></div>
            <div class="grupo-filtro filter-group"><label for="funcionario_id">Funcionário</label><select id="funcionario_id" name="funcionario_id"><option value="">Todos</option><?php foreach ($listaFuncionarios as $funcionario): ?><option value="<?= (int) $funcionario['id'] ?>" <?= $funcionarioId === (int) $funcionario['id'] ? 'selected' : '' ?>><?= escaparRelatorio($funcionario['nome']) ?></option><?php endforeach; ?></select></div>
            <div class="grupo-filtro filter-group"><label for="situacao">Situação</label><select id="situacao" name="situacao"><option value="todos" <?= $situacao === 'todos' ? 'selected' : '' ?>>Todas</option><option value="pago" <?= $situacao === 'pago' ? 'selected' : '' ?>>Pagos</option><option value="pendente" <?= $situacao === 'pendente' ? 'selected' : '' ?>>Pendentes</option></select></div>
            <div class="acoes-filtro filter-actions"><a href="relatorio.php" class="botao botao-vazio btn btn-outline">Limpar</a><button type="submit" class="botao botao-cheio btn btn-solid"><i class="fa-solid fa-filter"></i> Aplicar filtros</button></div>
        </form>
        <div class="acoes-relatorio"><span><?= (int) $resumo['quantidade'] ?> pagamento(s) no período selecionado</span><a class="botao botao-cheio btn btn-solid" href="<?= escaparRelatorio($urlExportar) ?>"><i class="fa-solid fa-download"></i> Gerar CSV</a></div>
        <section class="resumos kpi-section">
            <div class="cartao kpi-card"><div class="icone-cartao icone-verde kpi-icon icon-green"><i class="fa-solid fa-dollar-sign"></i></div><div class="detalhes-cartao kpi-details"><span class="titulo-cartao kpi-title">Total pago</span><strong class="valor-cartao kpi-value texto-verde text-green">R$ <?= number_format($totalPago, 2, ',', '.') ?></strong><span class="sub-cartao kpi-sub">No período filtrado</span></div></div>
            <div class="cartao kpi-card"><div class="icone-cartao icone-laranja kpi-icon icon-orange"><i class="fa-regular fa-clock"></i></div><div class="detalhes-cartao kpi-details"><span class="titulo-cartao kpi-title">Total pendente</span><strong class="valor-cartao kpi-value texto-laranja text-orange">R$ <?= number_format($totalPendente, 2, ',', '.') ?></strong><span class="sub-cartao kpi-sub">No período filtrado</span></div></div>
            <div class="cartao kpi-card"><div class="icone-cartao icone-azul kpi-icon icon-blue"><i class="fa-regular fa-user"></i></div><div class="detalhes-cartao kpi-details"><span class="titulo-cartao kpi-title">Funcionários com pagamentos</span><strong class="valor-cartao kpi-value texto-azul text-blue"><?= (int) $resumo['funcionarios'] ?></strong><span class="sub-cartao kpi-sub">Dentro dos filtros</span></div></div>
            <div class="cartao kpi-card"><div class="icone-cartao icone-roxo kpi-icon icon-purple"><i class="fa-solid fa-list-check"></i></div><div class="detalhes-cartao kpi-details"><span class="titulo-cartao kpi-title">Pagamentos no período</span><strong class="valor-cartao kpi-value texto-roxo text-purple"><?= (int) $resumo['quantidade'] ?></strong><span class="sub-cartao kpi-sub">Registros encontrados</span></div></div>
        </section>
        <section class="graficos charts-section">
            <div class="caixa-grafico chart-container"><h3>Pagamentos por mês</h3><div class="grafico-mensal" role="img" aria-label="Gráfico mensal de pagamentos pagos e pendentes">
                <?php if ($meses): ?><svg viewBox="0 0 720 250" preserveAspectRatio="none" aria-hidden="true"><line x1="35" y1="205" x2="710" y2="205" stroke="#e5e7eb" /><?php $larguraGrupo = 660 / count($meses); foreach (array_values($meses) as $indice => $mes): $alturaPago = ($mes['pago'] / $maiorValorGrafico) * 165; $alturaPendente = ($mes['pendente'] / $maiorValorGrafico) * 165; $xGrupo = 45 + ($indice * $larguraGrupo); ?><rect x="<?= $xGrupo ?>" y="<?= 205 - $alturaPago ?>" width="<?= max(4, $larguraGrupo * .28) ?>" height="<?= $alturaPago ?>" rx="3" fill="#22c55e"><title>Pago: R$ <?= number_format($mes['pago'], 2, ',', '.') ?></title></rect><rect x="<?= $xGrupo + $larguraGrupo * .34 ?>" y="<?= 205 - $alturaPendente ?>" width="<?= max(4, $larguraGrupo * .28) ?>" height="<?= $alturaPendente ?>" rx="3" fill="#f97316"><title>Pendente: R$ <?= number_format($mes['pendente'], 2, ',', '.') ?></title></rect><text x="<?= $xGrupo + $larguraGrupo * .32 ?>" y="230" text-anchor="middle"><?= escaparRelatorio($mes['label']) ?></text><?php endforeach; ?></svg><div class="legenda-grafico"><span><i class="ponto ponto-verde"></i> Pago</span><span><i class="ponto ponto-laranja"></i> Pendente</span></div><?php else: ?><p class="sem-dados-grafico">Não há pagamentos neste período.</p><?php endif; ?>
            </div></div>
            <div class="caixa-grafico chart-container"><h3>Pagamentos por situação</h3><div class="area-rosca canvas-wrapper donut-wrapper"><div class="donut-chart-box"><div class="rosca-grafico" role="img" aria-label="<?= number_format($percentualPago, 1, ',', '') ?>% pagos e <?= number_format($percentualPendente, 1, ',', '') ?>% pendentes" style="background: <?= $somaTotal > 0 ? 'conic-gradient(#22c55e 0 ' . $percentualPago . '%, #ea580c ' . $percentualPago . '% 100%)' : '#e5e7eb' ?>;"></div><div class="donut-center-info"><span class="donut-center-title">Total</span><strong class="donut-center-val">R$ <?= number_format($somaTotal, 2, ',', '.') ?></strong></div></div><div class="legenda-rosca donut-legend"><div class="item-legenda legend-item"><span class="ponto ponto-verde dot dot-green"></span><div class="texto-legenda legend-text"><span>Pagamentos realizados</span><strong>R$ <?= number_format($totalPago, 2, ',', '.') ?> <small>(<?= number_format($percentualPago, 1, ',', '.') ?>%)</small></strong></div></div><div class="item-legenda legend-item"><span class="ponto ponto-laranja dot dot-orange"></span><div class="texto-legenda legend-text"><span>Pagamentos pendentes</span><strong>R$ <?= number_format($totalPendente, 2, ',', '.') ?> <small>(<?= number_format($percentualPendente, 1, ',', '.') ?>%)</small></strong></div></div></div></div></div>
        </section>
        <section class="tabelas tables-section">
            <div class="caixa-tabela table-container"><h3>Maiores pagamentos realizados</h3><div class="tabela-scroll"><table><thead><tr><th>#</th><th>Funcionário</th><th>Cargo</th><th>Valor</th><th>Data</th></tr></thead><tbody><?php if (!$maioresPagamentos): ?><tr><td colspan="5">Nenhum pagamento realizado encontrado.</td></tr><?php else: foreach ($maioresPagamentos as $i => $row): ?><tr><td><?= $i + 1 ?></td><td><?= escaparRelatorio($row['nome']) ?></td><td><?= escaparRelatorio($row['cargo']) ?></td><td>R$ <?= number_format((float) $row['valor'], 2, ',', '.') ?></td><td><?= dataValida((string) $row['data']) ? date('d/m/Y', strtotime($row['data'])) : escaparRelatorio($row['data']) ?></td></tr><?php endforeach; endif; ?></tbody></table></div><a class="btn-ver-todos btn-view-all" href="<?= escaparRelatorio($urlVerTodos) ?>"><?= $verTodos ? 'Mostrar menos' : 'Ver todos' ?></a></div>
            <div class="caixa-tabela table-container"><h3>Pagamentos pendentes</h3><div class="tabela-scroll"><table><thead><tr><th>#</th><th>Funcionário</th><th>Cargo</th><th>Valor</th><th>Data prevista</th><th>Atraso</th></tr></thead><tbody><?php if (!$pendentes): ?><tr><td colspan="6">Nenhum pagamento pendente encontrado.</td></tr><?php else: foreach ($pendentes as $i => $row): ?><tr><td><?= $i + 1 ?></td><td><?= escaparRelatorio($row['nome']) ?></td><td><?= escaparRelatorio($row['cargo']) ?></td><td>R$ <?= number_format((float) $row['valor'], 2, ',', '.') ?></td><td><?= dataValida((string) $row['data_prevista']) ? date('d/m/Y', strtotime($row['data_prevista'])) : escaparRelatorio($row['data_prevista']) ?></td><td class="<?= (int) $row['dias_atraso'] > 0 ? 'texto-vermelho text-red' : '' ?>"><?= (int) $row['dias_atraso'] > 0 ? (int) $row['dias_atraso'] . ' dias' : 'No prazo' ?></td></tr><?php endforeach; endif; ?></tbody></table></div><a class="btn-ver-todos btn-view-all" href="<?= escaparRelatorio($urlVerTodos) ?>"><?= $verTodos ? 'Mostrar menos' : 'Ver todos' ?></a></div>
        </section>
        <footer class="rodape main-footer"><i class="fa-solid fa-circle-info"></i> Dados atualizados em <?= date('d/m/Y H:i') ?>.</footer>
    </main>
</div>
</body>
</html>
