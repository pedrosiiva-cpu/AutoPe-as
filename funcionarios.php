<?php
declare(strict_types=1);
session_start();
require_once 'auth.php';
exigirLogin();

require 'init.php';
require 'crud.php';
require 'funcoes.php';

$busca = trim((string) ($_GET['busca'] ?? ''));
$situacao = (string) ($_GET['situacao'] ?? 'Todos');
$cargoFiltro = (string) ($_GET['cargo'] ?? 'Todos');
$paginaAtual = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 8;

$mapaStatus = [
    'Ativo' => 'ativo',
    'Férias' => 'ferias',
    'Inativo' => 'inativo',
];

$condicoes = [];


if ($busca !== '') {
    $condicoes[] = 'nome LIKE ' . $pdo->quote('%' . $busca . '%');
}
if ($situacao !== 'Todos' && isset($mapaStatus[$situacao])) {
    $condicoes[] = 'status = ' . $pdo->quote($mapaStatus[$situacao]);
}
if ($cargoFiltro !== 'Todos') {
    $condicoes[] = 'cargo = ' . $pdo->quote($cargoFiltro);
}

$whereBase = $condicoes ? implode(' AND ', $condicoes) : '1=1';


$stmtTotal = $pdo->query("SELECT COUNT(*) FROM funcionarios WHERE {$whereBase}");
$totalRegistros = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$paginaAtual = min($paginaAtual, $totalPaginas);
$offset = ($paginaAtual - 1) * $porPagina;


$whereFinal = "{$whereBase} ORDER BY nome ASC LIMIT {$porPagina} OFFSET {$offset}";
$funcionarios = readAll($pdo, 'funcionarios', $whereFinal);

$flash = obterFlash();


function linkPagina(int $pagina): string
{
    $query = $_GET;
    $query['pagina'] = $pagina;
    return '?' . http_build_query($query);
}


$paginasVisiveis = [1];
for ($p = $paginaAtual - 1; $p <= $paginaAtual + 1; $p++) {
    if ($p > 1 && $p < $totalPaginas) {
        $paginasVisiveis[] = $p;
    }
}
if ($totalPaginas > 1) {
    $paginasVisiveis[] = $totalPaginas;
}
$paginasVisiveis = array_values(array_unique($paginasVisiveis));
sort($paginasVisiveis);

$cargosDisponiveis = ['Todos', 'Mecânico', 'Financeiro', 'Estoquista', 'Atendente', 'Vendedor', 'Auxiliar Administrativo', 'Consultora de Peças'];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/funcionarios.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <title>Funcionarios | AutoPeças</title>
</head>

<body>
    <div class="app">
       <div class="painel dashboard-container">
    <?php require __DIR__ . '/sidebar.php'; ?>

        <div class="main">
            <header class="topbar">
                <div class="page-title">
                    <h1>Funcionários</h1>

                </div>
                <div class="topbar-actions">
                    <div class="user-menu">
                        <div class="avatar">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 21c0-4 4-6 8-6s8 2 8 6" />
                            </svg>
                        </div>
                        <div>
                            <div class="user-name">Gestor Financeiro</div>
                            <div class="user-role">Administrador</div>
                        </div>
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </div>
                </div>
            </header>

            <main class="content">
                <?php if ($flash): ?>
                    <div class="alerta alerta-<?= htmlspecialchars($flash['tipo'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($flash['mensagem'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <div class="content-header">
                    <div>
                        <h2>Funcionários</h2>
                        <p>Visualize, cadastre e gerencie os funcionários.</p>
                    </div>
                    <a href="funcionario_form.php" class="btn-primary">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        NOVO FUNCIONÁRIO
                    </a>
                </div>

                <form method="get" action="funcionarios.php" class="filters-bar">
                    <div class="search-field">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <label for="busca" class="sr-only" style="position:absolute;left:-9999px;">Pesquisar
                            funcionário</label>
                        <input type="text" id="busca" name="busca" placeholder="Pesquisar funcionário..."
                            value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="select-field">
                        <label for="situacao">Situação</label>
                        <select id="situacao" name="situacao">
                            <?php foreach (['Todos', 'Ativo', 'Férias', 'Inativo'] as $opcao): ?>
                                <option <?= $situacao === $opcao ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="select-field">
                        <label for="cargo">Cargo</label>
                        <select id="cargo" name="cargo">
                            <?php foreach ($cargosDisponiveis as $opcao): ?>
                                <option <?= $cargoFiltro === $opcao ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($opcao, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-outline">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        FILTRAR
                    </button>
                    <a href="funcionarios.php" class="btn-outline">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 4 23 10 17 10" />
                            <polyline points="1 20 1 14 7 14" />
                            <path d="M3.5 9a9 9 0 0 1 15-3.5L23 10M1 14l4.5 4.5A9 9 0 0 0 20.5 15" />
                        </svg>
                        LIMPAR FILTROS
                    </a>
                </form>

                <div class="table-card">
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Cargo</th>
                                    <th>Data de admissão</th>
                                    <th>Salário</th>
                                    <th>Situação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$funcionarios): ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center;color:var(--text-muted);padding:32px;">
                                            Nenhum funcionário encontrado para os filtros selecionados.
                                        </td>
                                    </tr>
                                <?php else:
                                    foreach ($funcionarios as $f): ?>
                                        <tr>
                                            <td class="id-col"><?= str_pad((string) $f['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                            <td><?= htmlspecialchars($f['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($f['cargo'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= formatarDataBr($f['data_admissao']) ?></td>
                                            <td><?= formatarMoeda((float) $f['salario_base']) ?></td>
                                            <td><span
                                                    class="status-pill <?= classeStatus($f['status']) ?>"><?= rotuloStatus($f['status']) ?></span>
                                            </td>
                                            <td>
                                                <div class="actions">
                                                    <a class="action-btn"
                                                        href="funcionario_form.php?id=<?= (int) $f['id'] ?>&modo=visualizar"
                                                        aria-label="Visualizar <?= htmlspecialchars($f['nome'], ENT_QUOTES, 'UTF-8') ?>">
                                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" />
                                                            <circle cx="12" cy="12" r="3" />
                                                        </svg>
                                                    </a>
                                                    <a class="action-btn" href="funcionario_form.php?id=<?= (int) $f['id'] ?>"
                                                        aria-label="Editar <?= htmlspecialchars($f['nome'], ENT_QUOTES, 'UTF-8') ?>">
                                                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M12 20h9" />
                                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                                        </svg>
                                                    </a>
                                                    <form method="post" action="funcionario_excluir.php" style="display:inline;"
                                                        onsubmit="return confirm('Excluir <?= htmlspecialchars($f['nome'], ENT_QUOTES, 'UTF-8') ?>? Esta ação não pode ser desfeita.');">
                                                        <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= gerarTokenCsrf() ?>">
                                                        <button type="submit" class="action-btn delete"
                                                            aria-label="Excluir <?= htmlspecialchars($f['nome'], ENT_QUOTES, 'UTF-8') ?>">
                                                            <svg class="icon" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round">
                                                                <polyline points="3 6 5 6 21 6" />
                                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                                                <path d="M10 11v6" />
                                                                <path d="M14 11v6" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <span>
                            <?php if ($totalRegistros > 0): ?>
                                Mostrando <?= $offset + 1 ?> a <?= min($offset + $porPagina, $totalRegistros) ?> de
                                <?= $totalRegistros ?> funcionários
                            <?php else: ?>
                                Nenhum funcionário cadastrado
                            <?php endif; ?>
                        </span>
                        <div class="pagination">
                            <?php
                            $iconPrimeira = '<svg class="icon" style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/></svg>';
                            $iconAnterior = '<svg class="icon" style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>';
                            $iconProxima = '<svg class="icon" style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>';
                            $iconUltima = '<svg class="icon" style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="13 17 18 12 13 7"/><polyline points="6 17 11 12 6 7"/></svg>';

                            function botaoPagina(bool $ativoLink, string $href, string $label, string $conteudoHtml): void
                            {
                                if ($ativoLink) {
                                    echo '<a class="page-btn" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" aria-label="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">' . $conteudoHtml . '</a>';
                                } else {
                                    echo '<button class="page-btn" disabled aria-label="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">' . $conteudoHtml . '</button>';
                                }
                            }

                            botaoPagina($paginaAtual > 1, linkPagina(1), 'Primeira página', $iconPrimeira);
                            botaoPagina($paginaAtual > 1, linkPagina(max(1, $paginaAtual - 1)), 'Página anterior', $iconAnterior);

                            $anterior = null;
                            foreach ($paginasVisiveis as $p) {
                                if ($anterior !== null && $p - $anterior > 1) {
                                    echo '<span class="page-dots">…</span>';
                                }
                                if ($p === $paginaAtual) {
                                    echo '<button class="page-btn active">' . $p . '</button>';
                                } else {
                                    echo '<a class="page-btn" href="' . htmlspecialchars(linkPagina($p), ENT_QUOTES, 'UTF-8') . '">' . $p . '</a>';
                                }
                                $anterior = $p;
                            }

                            botaoPagina($paginaAtual < $totalPaginas, linkPagina(min($totalPaginas, $paginaAtual + 1)), 'Próxima página', $iconProxima);
                            botaoPagina($paginaAtual < $totalPaginas, linkPagina($totalPaginas), 'Última página', $iconUltima);
                            ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
