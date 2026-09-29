<?php
$totalPago = 82450.00;
$totalPendente = 15300.00;
$totalFuncionarios = 48;
$mediaSalarial = 2280.56;
$labelsMeses = ["Mai/2026", "Jun/2026", "Jul/2026", "Ago/2026", "Set/2026"];
$valoresPago = [24000, 26500, 31000, 33000, 25000];
$valoresPendente = [7000, 6000, 7500, 6800, 4500];
$somaTotal = $totalPago + $totalPendente;
$percPago = $somaTotal > 0 ? number_format(($totalPago / $somaTotal) * 100, 1, ',', '') : '0,0';
$percPendente = $somaTotal > 0 ? number_format(($totalPendente / $somaTotal) * 100, 1, ',', '') : '0,0';

$maioresPagamentos = [
    ["nome" => "Maria Oliveira", "cargo" => "Financeiro", "valor" => 2800.00, "data" => "2026-09-05"],
    ["nome" => "João da Silva", "cargo" => "Mecânico", "valor" => 2500.00, "data" => "2026-09-05"],
    ["nome" => "Lucas Ferreira", "cargo" => "Mecânico", "valor" => 2600.00, "data" => "2026-09-25"],
    ["nome" => "Ricardo Mendes", "cargo" => "Vendedor", "valor" => 2400.00, "data" => "2026-09-15"],
    ["nome" => "Carlos Santos", "cargo" => "Estoquista", "valor" => 2300.00, "data" => "2026-09-10"],
];

$pendentes = [
    ["nome" => "Carlos Santos", "cargo" => "Estoquista", "valor" => 2300.00, "data_prevista" => "2026-09-10", "dias_atraso" => 5],
    ["nome" => "Ana Paula", "cargo" => "Atendente", "valor" => 2000.00, "data_prevista" => "2026-09-10", "dias_atraso" => 5],
    ["nome" => "Fernanda Lima", "cargo" => "Aux. Administrativo", "valor" => 2100.00, "data_prevista" => "2026-09-20", "dias_atraso" => 0],
    ["nome" => "Lucas Ferreira", "cargo" => "Mecânico", "valor" => 2600.00, "data_prevista" => "2026-09-25", "dias_atraso" => 0],
    ["nome" => "Patricia Souza", "cargo" => "Consult. de Peças", "valor" => 2200.00, "data_prevista" => "2026-09-30", "dias_atraso" => 0],
];
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="funcionarios.html" class="link nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
                <a href="#" class="link nav-link"><i class="fa-solid fa-users"></i> Funcionários</a>
                <a href="#" class="link nav-link"><i class="fa-solid fa-dollar-sign"></i> Pagamentos</a>
                <a href="#" class="link nav-link"><i class="fa-regular fa-bell"></i> Prazos e Alertas</a>
                <a href="#" class="link nav-link ativo active"><i class="fa-solid fa-chart-column"></i> Relatórios</a>
                <a href="#" class="link nav-link"><i class="fa-solid fa-gear"></i> Configurações</a>
            </nav>
            <div class="menu-rodape sidebar-footer">
                <a href="#" class="link nav-link texto-vermelho text-red"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sair</a>
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

            <section class="filtros filters-section">
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
                    <select>
                        <option>Todos</option>
                    </select>
                </div>
                <div class="acoes-filtro filter-actions">
                    <button class="botao botao-vazio btn btn-outline"><i class="fa-solid fa-rotate-right"></i> LIMPAR FILTROS</button>
                    <button class="botao botao-cheio btn btn-solid"><i class="fa-solid fa-download"></i> GERAR RELATÓRIO</button>
                </div>
            </section>

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
                    <div class="canvas-wrapper" style="height: 250px;">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
                <div class="caixa-grafico chart-container">
                    <h3>Pagamentos por situação</h3>
                    <div class="area-rosca canvas-wrapper donut-wrapper">
                        <div class="donut-chart-box">
                            <canvas id="donutChart"></canvas>
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

    <script>
    const labelsMeses = <?= json_encode($labelsMeses); ?>;
    const valoresPago = <?= json_encode($valoresPago); ?>;
    const valoresPendente = <?= json_encode($valoresPendente); ?>;

    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: labelsMeses,
            datasets: [
                {
                    label: 'Pago',
                    data: valoresPago,
                    backgroundColor: '#22c55e',
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.6
                },
                {
                    label: 'Pendente',
                    data: valoresPendente,
                    backgroundColor: '#f97316',
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        boxHeight: 12,
                        usePointStyle: false,
                        font: { size: 12, family: 'Inter' },
                        color: '#374151',
                        padding: 15
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 40000,
                    ticks: {
                        stepSize: 10000,
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        },
                        font: { size: 11, family: 'Inter' },
                        color: '#6b7280'
                    },
                    grid: {
                        color: '#f3f4f6'
                    },
                    border: {
                        display: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 11, family: 'Inter' },
                        color: '#6b7280'
                    },
                    border: {
                        color: '#e5e7eb'
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('donutChart'), {
        type: 'doughnut',
        data: {
            labels: ['Pagamentos realizados', 'Pagamentos pendentes'],
            datasets: [{
                data: [<?= $totalPago; ?>, <?= $totalPendente; ?>],
                backgroundColor: ['#22c55e', '#ea580c'],
                borderWidth: 0,
                hoverOffset: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' R$ ' + Number(context.raw).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                        }
                    }
                }
            },
            cutout: '72%'
        }
    });
    </script>
</body>
</html>