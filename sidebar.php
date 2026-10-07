<?php
$paginaSidebar = basename($_SERVER['PHP_SELF'] ?? '');
$itemSidebarAtivo = match ($paginaSidebar) {
    'dashboard.php' => 'dashboard',
    'funcionarios.php', 'funcionario_form.php', 'editaSenha.php' => 'funcionarios',
    'pagamentos.php', 'cadastrar_pagamento.php', 'editar_pagamento.php', 'excluir_pagamento.php', 'visualizar_pagamento.php' => 'pagamentos',
    'prazos_alertas.php' => 'prazos',
    'relatorio.php' => 'relatorios',
    default => '',
};
?>
<aside class="app-sidebar" aria-label="Navegação principal">
    <div class="app-sidebar-header">
        <a href="dashboard.php" class="app-sidebar-logo" aria-label="AutoPeças, início">
            <img src="imagens/remove.png" alt="AutoPeças">
        </a>
    </div>
    <nav class="app-sidebar-nav">
        <a href="dashboard.php" class="app-sidebar-link<?= $itemSidebarAtivo === 'dashboard' ? ' active' : '' ?>"<?= $itemSidebarAtivo === 'dashboard' ? ' aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5M5.5 9.5V21h13V9.5M9 21v-6h6v6"/></svg>
            <span>Dashboard</span>
        </a>
        <a href="funcionarios.php" class="app-sidebar-link<?= $itemSidebarAtivo === 'funcionarios' ? ' active' : '' ?>"<?= $itemSidebarAtivo === 'funcionarios' ? ' aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="7" r="4"/><path d="M2 21v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1M16.5 3.2a4 4 0 0 1 0 7.6M22 21v-1a5 5 0 0 0-3.5-4.8"/></svg>
            <span>Funcionários</span>
        </a>
        <a href="pagamentos.php" class="app-sidebar-link<?= $itemSidebarAtivo === 'pagamentos' ? ' active' : '' ?>"<?= $itemSidebarAtivo === 'pagamentos' ? ' aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2.5"/><path d="M2 10h20"/></svg>
            <span>Pagamentos</span>
        </a>
        <a href="prazos_alertas.php" class="app-sidebar-link<?= $itemSidebarAtivo === 'prazos' ? ' active' : '' ?>"<?= $itemSidebarAtivo === 'prazos' ? ' aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
            <span>Prazos e alertas</span>
        </a>
        <a href="relatorio.php" class="app-sidebar-link<?= $itemSidebarAtivo === 'relatorios' ? ' active' : '' ?>"<?= $itemSidebarAtivo === 'relatorios' ? ' aria-current="page"' : '' ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V11M10 20V5M16 20v-7M22 20V8"/></svg>
            <span>Relatórios</span>
        </a>
    </nav>
    <div class="app-sidebar-footer">
        <a href="logout.php" class="app-sidebar-link logout">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5M16 17l5-5-5-5M21 12H9"/></svg>
            <span>Sair</span>
        </a>
    </div>
</aside>
