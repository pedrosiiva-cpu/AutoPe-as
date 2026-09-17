<?php
$totalPago = 82450.00;
$totalPendente = 15300.00;
$totalFuncionarios = 48;
$mediaSalarial = 2280.56;
$labelsMeses = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set"];
$valoresMeses = [4200, 5100, 4800, 6200, 7000, 7500, 8100, 9200, 8850];
$somaTotal = $totalPago + $totalPendente;
$percPago = $somaTotal > 0 ? round(($totalPago / $somaTotal) * 100, 1) : 0;
$percPendente = $somaTotal > 0 ? round(($totalPendente / $somaTotal) * 100, 1) : 0;

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
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="./imagens/remove.png" alt="AutoPeças" class="logo-img">
            </div>
            <nav class="sidebar-nav">
                <a href="#" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
                <a href="#" class="nav-link"><i class="fa-solid fa-users"></i> Funcionários</a>
                <a href="#" class="nav-link"><i class="fa-solid fa-dollar-sign"></i> Pagamentos</a>
                <a href="#" class="nav-link"><i class="fa-regular fa-bell"></i> Prazos e Alertas</a>
                <a href="#" class="nav-link active"><i class="fa-solid fa-chart-column"></i> Relatórios</a>
                <a href="#" class="nav-link"><i class="fa-solid fa-gear"></i> Configurações</a>
            </nav>
            <div class="sidebar-footer">
                <a href="#" class="nav-link text-red"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sair</a>
            </div>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="menu-btn"><i class="fa-solid fa-bars"></i></button>
                    <div>
                        <h1>Relatórios</h1>
                        <p class="subtitle">Veja análises e relatórios financeiros da empresa.</p>
                    </div>
                </div>
                <div class="topbar-right">
                    <div class="notification">
                        <i class="fa-regular fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                    <div class="user-profile">
                        <div class="avatar"><i class="fa-regular fa-user"></i></div>
                        <div class="user-info">
                            <strong>Gestor Financeiro</strong>
                            <span>Administrador</span>
                        </div>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                </div>
            </header>

            <section class="filters-section">
                <div class="filter-group">
                    <label>Tipo de relatório</label>
                    <select>
                        <option>Resumo financeiro</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Período</label>
                    <div class="input-with-icon">
                        <input type="text" value="01/08/2026 - 30/09/2026" readonly>
                        <i class="fa-regular fa-calendar"></i>
                    </div>
                </div>
                <div class="filter-group">
                    <label>Funcionário</label>
                    <select>
                        <option>Todos</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-outline"><i class="fa-solid fa-rotate-right"></i> LIMPAR FILTROS</button>
                    <button class="btn btn-solid"><i class="fa-solid fa-download"></i> GERAR RELATÓRIO</button>
                </div>
            </section>

            <section class="kpi-section">
                <div class="kpi-card">
                    <div class="kpi-icon icon-green"><i class="fa-solid fa-dollar-sign"></i></div>
                    <div class="kpi-details">
                        <span class="kpi-title">Total pago no período</span>
                        <strong class="kpi-value text-green">R$ <?= number_format($totalPago, 2, ',', '.'); ?></strong>
                        <span class="kpi-sub">Pagamentos realizados</span>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon icon-orange"><i class="fa-regular fa-clock"></i></div>
                    <div class="kpi-details">
                        <span class="kpi-title">Total pendente</span>
                        <strong class="kpi-value text-orange">R$ <?= number_format($totalPendente, 2, ',', '.'); ?></strong>
                        <span class="kpi-sub">Pagamentos não realizados</span>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon icon-blue"><i class="fa-regular fa-user"></i></div>
                    <div class="kpi-details">
                        <span class="kpi-title">Total de funcionários</span>
                        <strong class="kpi-value text-blue"><?= $totalFuncionarios; ?></strong>
                        <span class="kpi-sub">Funcionários cadastrados</span>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon icon-purple"><i class="fa-regular fa-calendar-days"></i></div>
                    <div class="kpi-details">
                        <span class="kpi-title">Média salarial</span>
                        <strong class="kpi-value text-purple">R$ <?= number_format($mediaSalarial, 2, ',', '.'); ?></strong>
                        <span class="kpi-sub">Salário médio da equipe</span>
                    </div>
                </div>
            </section>

            <section class="charts-section">
                <div class="chart-container">
                    <h3>Pagamentos por mês</h3>
                    <div class="canvas-wrapper" style="height: 250px;">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
                <div class="chart-container">
                    <h3>Pagamentos por situação</h3>
                    <div class="canvas-wrapper donut-wrapper" style="height: 250px; position: relative; display: flex; align-items: center; justify-content: center; gap: 20px; flex-direction: row;">
                        <div style="width: 200px; height: 200px;">
                            <canvas id="donutChart"></canvas>
                        </div>
                        <div class="donut-legend" style="width: auto;">
                            <div class="legend-item">
                                <span class="dot dot-green"></span>
                                <div class="legend-text">
                                    <span>Pagamentos realizados</span>
                                    <strong>R$ <?= number_format($totalPago, 2, ',', '.'); ?> <small>(<?= $percPago; ?>%)</small></strong>
                                </div>
                            </div>
                            <div class="legend-item" style="margin-top: 15px;">
                                <span class="dot dot-orange"></span>
                                <div class="legend-text">
                                    <span>Pagamentos pendentes</span>
                                    <strong>R$ <?= number_format($totalPendente, 2, ',', '.'); ?> <small>(<?= $percPendente; ?>%)</small></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="tables-section">
                <div class="table-container">
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
                    <button class="btn-view-all">VER TODOS</button>
                </div>

                <div class="table-container">
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
                                <td class="<?= $row['dias_atraso'] > 0 ? 'text-red' : ''; ?>">
                                    <?= $row['dias_atraso'] > 0 ? $row['dias_atraso'] . ' dias' : '-'; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button class="btn-view-all">VER TODOS</button>
                </div>
            </section>

            <footer class="main-footer">
                <i class="fa-solid fa-circle-info"></i> Relatórios atualizados em tempo real. Última atualização: <?= date('d/m/Y H:i'); ?>
            </footer>
        </main>
    </div>

    <script>
    const labelsMeses = <?= json_encode($labelsMeses); ?>;
    const valoresMeses = <?= json_encode($valoresMeses); ?>;

    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: labelsMeses,
            datasets: [{
                label: 'Pagamentos (R$)',
                data: valoresMeses,
                backgroundColor: '#3b82f6',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    new Chart(document.getElementById('donutChart'), {
        type: 'doughnut',
        data: {
            labels: ['Pago', 'Pendente'],
            datasets: [{
                data: [<?= $totalPago; ?>, <?= $totalPendente; ?>],
                backgroundColor: ['#22c55e', '#f97316'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            cutout: '70%'
        }
    });
    </script>
</body>
</html>