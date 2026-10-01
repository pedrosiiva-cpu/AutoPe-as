<?php
require_once __DIR__ . '/auth.php';
exigirLogin();
require_once __DIR__ . '/init.php';
$listaFuncionarios = $pdo->query('SELECT id, nome FROM funcionarios ORDER BY nome')->fetchAll();
$funcionarioId = filter_input(INPUT_GET, 'funcionario_id', FILTER_VALIDATE_INT) ?: null;
if ($funcionarioId !== null && !in_array($funcionarioId, array_column($listaFuncionarios, 'id'), false)) {
    $funcionarioId = null;
}
$wherePagamento = $funcionarioId !== null ? ' AND p.funcionario_id = :funcionario_id' : '';
$parametros = $funcionarioId !== null ? ['funcionario_id' => $funcionarioId] : [];
$sqlResumoFuncionarios = 'SELECT COUNT(*) total, COALESCE(AVG(salario_base), 0) media FROM funcionarios';
if ($funcionarioId !== null) {
    $sqlResumoFuncionarios .= ' WHERE id = :funcionario_id';
}
$stmtFuncionarios = $pdo->prepare($sqlResumoFuncionarios);
$stmtFuncionarios->execute($parametros);
$resumoFuncionarios = $stmtFuncionarios->fetch();
$totalFuncionarios = (int) $resumoFuncionarios['total'];
$mediaSalarial = (float) $resumoFuncionarios['media'];
$labelsMeses = $valoresPago = $valoresPendente = [];
$stmtMeses = $pdo->prepare("SELECT DATE_FORMAT(p.data_pagamento, '%m/%Y') mes, SUM(CASE WHEN p.status = 'pago' THEN p.valor ELSE 0 END) pago, SUM(CASE WHEN p.status = 'pendente' THEN p.valor ELSE 0 END) pendente FROM pagamentos p WHERE p.data_pagamento >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH){$wherePagamento} GROUP BY YEAR(p.data_pagamento), MONTH(p.data_pagamento) ORDER BY YEAR(p.data_pagamento), MONTH(p.data_pagamento)");
$stmtMeses->execute($parametros);
foreach ($stmtMeses->fetchAll() as $mes) { $labelsMeses[] = $mes['mes']; $valoresPago[] = (float) $mes['pago']; $valoresPendente[] = (float) $mes['pendente']; }
$stmtTotais = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN p.status = 'pago' THEN p.valor ELSE 0 END), 0) pago, COALESCE(SUM(CASE WHEN p.status = 'pendente' THEN p.valor ELSE 0 END), 0) pendente FROM pagamentos p WHERE 1=1{$wherePagamento}");
$stmtTotais->execute($parametros);
$totais = $stmtTotais->fetch();
$totalPago = (float) $totais['pago'];
$totalPendente = (float) $totais['pendente'];
$somaTotal = $totalPago + $totalPendente;
$percPago = $somaTotal > 0 ? number_format(($totalPago / $somaTotal) * 100, 1, ',', '') : '0,0';
$percPendente = $somaTotal > 0 ? number_format(($totalPendente / $somaTotal) * 100, 1, ',', '') : '0,0';

$stmtMaiores = $pdo->prepare("SELECT f.nome, f.cargo, p.valor, p.data_pagamento AS data FROM pagamentos p JOIN funcionarios f ON f.id = p.funcionario_id WHERE p.status = 'pago'{$wherePagamento} ORDER BY p.valor DESC LIMIT 5");
$stmtMaiores->execute($parametros);
$maioresPagamentos = $stmtMaiores->fetchAll();
$stmtPendentes = $pdo->prepare("SELECT f.nome, f.cargo, p.valor, p.data_pagamento AS data_prevista, GREATEST(DATEDIFF(CURDATE(), p.data_pagamento), 0) dias_atraso FROM pagamentos p JOIN funcionarios f ON f.id = p.funcionario_id WHERE p.status = 'pendente'{$wherePagamento} ORDER BY p.data_pagamento ASC LIMIT 5");
$stmtPendentes->execute($parametros);
$pendentes = $stmtPendentes->fetchAll();
$exportar = ($_GET['exportar'] ?? '') === 'csv';
if ($exportar) {
    $stmtExportacao = $pdo->prepare("SELECT f.nome, f.cargo, p.valor, p.status, p.data_pagamento FROM pagamentos p JOIN funcionarios f ON f.id = p.funcionario_id WHERE 1=1{$wherePagamento} ORDER BY p.data_pagamento DESC");
    $stmtExportacao->execute($parametros);
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename=relatorio-pagamentos.csv');
    $saida = fopen('php://output', 'w');
    fwrite($saida, "\xEF\xBB\xBF");
    fputcsv($saida, ['Funcionário', 'Cargo', 'Valor', 'Status', 'Data'], ';');
    foreach ($stmtExportacao->fetchAll() as $linha) {
        fputcsv($saida, [$linha['nome'], $linha['cargo'], number_format((float) $linha['valor'], 2, ',', '.'), $linha['status'], $linha['data_pagamento']], ';');
    }
    fclose($saida);
    exit;
}
$maiorValorGrafico = max(array_merge([1.0], $valoresPago, $valoresPendente));
$percentualPagoGrafico = $somaTotal > 0 ? ($totalPago / $somaTotal) * 100 : 0;
$percentualPendenteGrafico = $somaTotal > 0 ? 100 - $percentualPagoGrafico : 0;
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
            <div class="menu-topo sidebar-header">
                <a href="#" class="link-logo">
                    <img src="./imagens/remove.png" alt="AutoPeças" class="logo logo-img">
                </a>
            </div>
            <nav class="navegacao sidebar-nav">
                <a href="dashboard.php" class="link nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
                <a href="funcionarios.php" class="link nav-link"><i class="fa-solid fa-users"></i> Funcionários</a>
                <a href="relatorio.php" class="link nav-link"><i class="fa-solid fa-dollar-sign"></i> Pagamentos</a>
                <a href="dashboard.php" class="link nav-link"><i class="fa-regular fa-bell"></i> Prazos e Alertas</a>
                <a href="#" class="link nav-link ativo active"><i class="fa-solid fa-chart-column"></i> Relatórios</a>
                <a href="#" class="link nav-link"><i class="fa-solid fa-gear"></i> Configurações</a>
            </nav>
            <div class="menu-rodape sidebar-footer">
                <a href="logout.php" class="link nav-link texto-vermelho text-red"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sair</a>
            </div>
        </aside>

        <main class="conteudo main-content">
            <header class="cabecalho topbar">
                <div class="cabecalho-esq topbar-left">
                    <button class="btn-menu menu-btn"><i class="fa-solid fa-bars"></i></button>
                    <div>
                        <h1>Relatórios</h1>
                        <p class="subtitulo subtitle">Veja análises e relatórios financeiros da empresa.</p>
                    </div>
                </div>
                <div class="cabecalho-dir topbar-right">
                    <div class="notificacao notification">
                        <i class="fa-regular fa-bell"></i>
                        <span class="alerta badge">3</span>
                    </div>
                    <div class="perfil user-profile">
                        <div class="foto avatar"><i class="fa-regular fa-user"></i></div>
                        <div class="info-usuario user-info">
                            <strong>Gestor Financeiro</strong>
                            <span>Administrador</span>
                        </div>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                </div>
            </header>

            <form method="get" action="relatorio.php" class="filtros filters-section">
                <div class="grupo-filtro filter-group">
                    <label>Tipo de relatório</label>
                    <select>
                        <option>Resumo financeiro</option>
                    </select>
                </div>
                <div class="grupo-filtro filter-group">
                    <label>Período</label>
                    <div class="input-icone input-with-icon">
                        <input type="text" value="01/08/2026 - 30/09/2026" readonly>
                        <i class="fa-regular fa-calendar"></i>
                    </div>
                </div>
                <div class="grupo-filtro filter-group">
                    <label>Funcionário</label>
                    <select id="funcionario_id" name="funcionario_id">
                        <option value="">Todos</option>
                        <?php foreach ($listaFuncionarios as $funcionario): ?>
                            <option value="<?= (int) $funcionario['id'] ?>" <?= $funcionarioId === (int) $funcionario['id'] ? 'selected' : '' ?>><?= htmlspecialchars($funcionario['nome'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="acoes-filtro filter-actions">
                    <a href="relatorio.php" class="botao botao-vazio btn btn-outline"><i class="fa-solid fa-rotate-right"></i> LIMPAR FILTROS</a>
                    <button type="submit" name="exportar" value="csv" class="botao botao-cheio btn btn-solid"><i class="fa-solid fa-download"></i> GERAR RELATÓRIO</button>
                </div>
            </form>

            <section class="resumos kpi-section">
                <div class="cartao kpi-card">
                    <div class="icone-cartao icone-verde kpi-icon icon-green"><i class="fa-solid fa-dollar-sign"></i></div>
                    <div class="detalhes-cartao kpi-details">
                        <span class="titulo-cartao kpi-title">Total pago no período</span>
                        <strong class="valor-cartao kpi-value texto-verde text-green">R$ <?= number_format($totalPago, 2, ',', '.'); ?></strong>
                        <span class="sub-cartao kpi-sub">Pagamentos realizados</span>
                    </div>
                </div>
                <div class="cartao kpi-card">
                    <div class="icone-cartao icone-laranja kpi-icon icon-orange"><i class="fa-regular fa-clock"></i></div>
                    <div class="detalhes-cartao kpi-details">
                        <span class="titulo-cartao kpi-title">Total pendente</span>
                        <strong class="valor-cartao kpi-value texto-laranja text-orange">R$ <?= number_format($totalPendente, 2, ',', '.'); ?></strong>
                        <span class="sub-cartao kpi-sub">Pagamentos não realizados</span>
                    </div>
                </div>
                <div class="cartao kpi-card">
                    <div class="icone-cartao icone-azul kpi-icon icon-blue"><i class="fa-regular fa-user"></i></div>
                    <div class="detalhes-cartao kpi-details">
                        <span class="titulo-cartao kpi-title">Total de funcionários</span>
                        <strong class="valor-cartao kpi-value texto-azul text-blue"><?= $totalFuncionarios; ?></strong>
                        <span class="sub-cartao kpi-sub">Funcionários cadastrados</span>
                    </div>
                </div>
                <div class="cartao kpi-card">
                    <div class="icone-cartao icone-roxo kpi-icon icon-purple"><i class="fa-regular fa-calendar-days"></i></div>
                    <div class="detalhes-cartao kpi-details">
                        <span class="titulo-cartao kpi-title">Média salarial</span>
                        <strong class="valor-cartao kpi-value texto-roxo text-purple">R$ <?= number_format($mediaSalarial, 2, ',', '.'); ?></strong>
                        <span class="sub-cartao kpi-sub">Salário médio da equipe</span>
                    </div>
                </div>
            </section>

            <section class="graficos charts-section">
                <div class="caixa-grafico chart-container">
                    <h3>Pagamentos por mês</h3>
                    <div class="grafico-mensal" role="img" aria-label="Gráfico de pagamentos pagos e pendentes por mês">
                        <?php if ($labelsMeses): ?>
                            <svg viewBox="0 0 720 250" preserveAspectRatio="none" aria-hidden="true">
                                <line x1="35" y1="205" x2="710" y2="205" stroke="#e5e7eb" />
                                <?php $larguraGrupo = 660 / count($labelsMeses); foreach ($labelsMeses as $indice => $mes):
                                    $alturaPago = ($valoresPago[$indice] / $maiorValorGrafico) * 165;
                                    $alturaPendente = ($valoresPendente[$indice] / $maiorValorGrafico) * 165;
                                    $xGrupo = 45 + ($indice * $larguraGrupo);
                                ?>
                                    <rect x="<?= $xGrupo ?>" y="<?= 205 - $alturaPago ?>" width="<?= max(8, $larguraGrupo * 0.28) ?>" height="<?= $alturaPago ?>" rx="3" fill="#22c55e"><title>Pago: R$ <?= number_format($valoresPago[$indice], 2, ',', '.') ?></title></rect>
                                    <rect x="<?= $xGrupo + $larguraGrupo * 0.34 ?>" y="<?= 205 - $alturaPendente ?>" width="<?= max(8, $larguraGrupo * 0.28) ?>" height="<?= $alturaPendente ?>" rx="3" fill="#f97316"><title>Pendente: R$ <?= number_format($valoresPendente[$indice], 2, ',', '.') ?></title></rect>
                                    <text x="<?= $xGrupo + $larguraGrupo * 0.32 ?>" y="230" text-anchor="middle"><?= htmlspecialchars($mes, ENT_QUOTES, 'UTF-8') ?></text>
                                <?php endforeach; ?>
                            </svg>
                            <div class="legenda-grafico"><span><i class="ponto ponto-verde"></i> Pago</span><span><i class="ponto ponto-laranja"></i> Pendente</span></div>
                        <?php else: ?>
                            <p class="sem-dados-grafico">Não há pagamentos nos últimos seis meses para este filtro.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="caixa-grafico chart-container">
                    <h3>Pagamentos por situação</h3>
                    <div class="area-rosca canvas-wrapper donut-wrapper">
                        <div class="donut-chart-box">
                            <div class="rosca-grafico" role="img" aria-label="Pagamentos: <?= number_format($percentualPagoGrafico, 1, ',', '') ?>% pagos e <?= number_format($percentualPendenteGrafico, 1, ',', '') ?>% pendentes" style="background: <?= $somaTotal > 0 ? 'conic-gradient(#22c55e 0 ' . $percentualPagoGrafico . '%, #ea580c ' . $percentualPagoGrafico . '% 100%)' : '#e5e7eb' ?>;"></div>
                            <div class="donut-center-info">
                                <span class="donut-center-title">Total</span>
                                <strong class="donut-center-val">R$ <?= number_format($somaTotal, 2, ',', '.'); ?></strong>
                            </div>
                        </div>
                        <div class="legenda-rosca donut-legend">
                            <div class="item-legenda legend-item">
                                <span class="ponto ponto-verde dot dot-green"></span>
                                <div class="texto-legenda legend-text">
                                    <span>Pagamentos realizados</span>
                                    <strong>R$ <?= number_format($totalPago, 2, ',', '.'); ?> <small>(<?= $percPago; ?>%)</small></strong>
                                </div>
                            </div>
                            <div class="item-legenda legend-item">
                                <span class="ponto ponto-laranja dot dot-orange"></span>
                                <div class="texto-legenda legend-text">
                                    <span>Pagamentos pendentes</span>
                                    <strong>R$ <?= number_format($totalPendente, 2, ',', '.'); ?> <small>(<?= $percPendente; ?>%)</small></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="tabelas tables-section">
                <div class="caixa-tabela table-container">
                    <h3>Maiores pagamentos do período</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Funcionário</th>
                                <th>Cargo</th>
                                <th>Valor</th>
                                <th>Data do pagamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($maioresPagamentos as $row): ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= htmlspecialchars($row['nome']); ?></td>
                                <td><?= htmlspecialchars($row['cargo']); ?></td>
                                <td>R$ <?= number_format($row['valor'], 2, ',', '.'); ?></td>
                                <td><?= date('d/m/Y', strtotime($row['data'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button class="btn-ver-todos btn-view-all">VER TODOS</button>
                </div>

                <div class="caixa-tabela table-container">
                    <h3>Pagamentos pendentes</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Funcionário</th>
                                <th>Cargo</th>
                                <th>Valor</th>
                                <th>Data prevista</th>
                                <th>Dias em atraso</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($pendentes as $row): ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= htmlspecialchars($row['nome']); ?></td>
                                <td><?= htmlspecialchars($row['cargo']); ?></td>
                                <td>R$ <?= number_format($row['valor'], 2, ',', '.'); ?></td>
                                <td><?= date('d/m/Y', strtotime($row['data_prevista'])); ?></td>
                                <td class="<?= $row['dias_atraso'] > 0 ? 'texto-vermelho text-red' : ''; ?>">
                                    <?= $row['dias_atraso'] > 0 ? $row['dias_atraso'] . ' dias' : '-'; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button class="btn-ver-todos btn-view-all">VER TODOS</button>
                </div>
            </section>

            <footer class="rodape main-footer">
                <i class="fa-solid fa-circle-info"></i> Relatórios atualizados em tempo real. Última atualização: <?= date('d/m/Y H:i'); ?>
            </footer>
        </main>
    </div>

</body>
</html>
