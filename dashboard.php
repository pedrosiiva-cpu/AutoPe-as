<<<<<<< HEAD
<?php

require_once "crud.php";

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

function dinheiro($valor)
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
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

$totalPago = $pagos['total'];
$totalPendente = $pendentes['total'];

$totalGeral = $totalPago + $totalPendente;

if ($totalGeral > 0) {
    $porcentagemPago = ($totalPago / $totalGeral) * 100;
    $porcentagemPendente = ($totalPendente / $totalGeral) * 100;
} else {
    $porcentagemPago = 0;
    $porcentagemPendente = 0;
}

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

    $valor = $resultado['total'];

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


?>

=======
>>>>>>> 4402300a28224e9b2f3c78d5724e958d7c7cdfd2
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>Dashboard</title>
</head>
<body>
    <main class="conteudo">
        <header class="cabecalho">
            <div class="titulo">
                <button class="botao-menu" type="button">
                    ☰
                </button>

                <div>
                    <h2>Dashboard</h2>
                    <p>Visão geral do sistema</p>
                </div>
            </div>

            <div class="usuario">
                <button type="button" class="botao-notificacao">
                    <i class="bi bi-bell-fill"></i>
                    <span class="numero-notificacao">3</span>
                </button>
                <div class="dados-usuario">
                    <div class="icone-usuario">
                        <i class="bi bi-person-circle"></i>
                    </div>

                    <div>
                        <strong>Gestor Financeiro</strong>
                        <span>Administrador</span>
                    </div>
                    <span class="seta"><i class="bi bi-arrow-down-short"></i></span>
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
                        <span class="titulo-card">Funcionários Cadastrados</span>
                        <strong class="valor-card">48</strong>
                        <div class="texto-card">
                            Ativos: 45
                            <span><i class="bi bi-dot"></i></span>
                            Inativos: 3
                        </div>
                    </div>
                </article>

                <article class="card">
                    <div class="icone-card"><i class="bi bi-wallet2"></i></div>

                    <div class="dados-card">
                        <span class="titulo-card">Pagamentos Realizados</span>

                        <strong class="valor-card">R$ 82.450,00</strong>

                        <div class="texto-card">Este mês</div>
                    </div>
                </article>

                <article class="card">
                    <div class="icone-card"><i class="bi bi-clock-fill"></i></div>

                    <div class="dados-card">
                        <span class="titulo-card">Pagamentos Pendentes</span>
                        <strong class="valor-card">R$15.300,00</strong>
                        <div class="texto-card">Este mês</div>
                    </div>
                </article>

                <article class="card">
                    <div class="icone-card"><i class="bi bi-calendar3"></i></div>

                    <div class="dados-card">
                        <span class="titulo-card">Próximos Pagamentos</span>
                        <strong class="valor-card">07</strong>
                        <div class="texto-card">Nos próximos 7 dias</div>
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
                                <tr>
                                    <td>
                                        <div class="funcionario">
                                            <div class="foto">JS</div>

                                            <div class="dados-funcionario">
                                                <strong>João da Silva</strong>

                                                <span>Mecânico</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        R$2.500,00
                                    </td>

                                    <td>
                                        05/09/2026
                                    </td>
                                    
                                    <td>
                                        <span class="situacao pago">Pago</span>
                                    </td>

                                    <td>
                                        <a href="#" class="botao-tres">:</a>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="funcionario">
                                            <div class="foto">JS</div>

                                            <div class="dados-funcionario">
                                                <strong>João da Silva</strong>

                                                <span>Mecânico</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        R$2.500,00
                                    </td>

                                    <td>
                                        05/09/2026
                                    </td>
                                    
                                    <td>
                                        <span class="situacao pendente">Pendente</span>
                                    </td>

                                    <td>
                                        <a href="#" class="botao-tres">:</a>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="funcionario">
                                            <div class="foto">JS</div>

                                            <div class="dados-funcionario">
                                                <strong>João da Silva</strong>

                                                <span>Mecânico</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        R$2.500,00
                                    </td>

                                    <td>
                                        05/09/2026
                                    </td>
                                    
                                    <td>
                                        <span class="situacao pago">Pago</span>
                                    </td>

                                    <td>
                                        <a href="#" class="botao-tres">:</a>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="funcionario">
                                            <div class="foto">JS</div>

                                            <div class="dados-funcionario">
                                                <strong>João da Silva</strong>

                                                <span>Mecânico</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        R$2.500,00
                                    </td>

                                    <td>
                                        05/09/2026
                                    </td>
                                    
                                    <td>
                                        <span class="situacao pendente">Pendente</span>
                                    </td>

                                    <td>
                                        <a href="#" class="botao-tres">:</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>                        
                    </div>
                </article>

                <article class="caixa alertas">

                    <div class="cabecalho-box">
                        <h3>Próximos prazos e alertas</h3>
                        <a href="#" class="botao-ver">Ver Todos</a>
                    </div>

                    <div class="bloco-atrasado">
                        <div class="titulo-alerta">
                            <div class="icone-alerta">
                                <span>!</span>
                                <strong>Pagamento(s) atrasados</strong>
                            </div>
                            <span class="quantidade">1</span>
                        </div>

                        <div class="item-alerta">
                            <div>
                                <strong>Carlos Santos - Estoquista</strong>
                                <span>Vencimento: 10/09/2026</span>
                            </div>
                            <strong>Atrasado há um dia</strong>
                        </div>
                    </div>

                    <div class="bloco-proximo">
                        <div class="titulo-alerta">
                            <div class="icone-alerta">
                                <span>!</span>
                                <strong>Próximos pagamentos</strong>
                            </div>
                            <span class="quantidade">3</span>
                        </div>

                        <div class="item-alerta">
                            <div>
                                <strong>Ana Paula - Atendente</strong>
                                <span>Vencimento: 11/09/2026</span>
                            </div>
                            <strong>Vence em um dia</strong>
                        </div>

                        <div class="item-alerta">
                            <div>
                                <strong>Ricardo Mendes - Vendedor</strong>
                                <span>Vencimento: 15/09/2026</span>
                            </div>
                            <strong>Vence em 6 dias</strong>
                        </div>

                        <div class="item-alerta">
                            <div>
                                <strong>João da Silva - Mecânico</strong>
                                <span>Vencimento: 05/10/2026</span>
                            </div>
                            <strong>Vence em 26 dias</strong>
                        </div>
                    </div>
                </article>
            </section>

            <section class="box resumo-financeiro">
                <div class="cabecalho-box">
                    <h3>Resumo financeiro (Este mês)</h3>
                    <select class="seletor-mes">
                        <option>Este mês</option>
                        <option>Mês passado</option>
                    </select>
                </div>

                <div class="resumo">
                    <div class="grafico-redondo">
                        <div class="valor-total">
                            <strong>R$ 97.750,00</strong>
                            <span>Total geral</span>
                        </div>
                    </div>

                    <div class="legenda">
                        <div class="item-legenda">
                            <span class="bolinha vermelha"><i class="bi bi-dot"></i></span>
                            <div>
                                <span>Pagamentos realizados</span>
                                <strong>R$ 82.450,00</strong>
                                <small>84,3%</small>
                            </div>
                        </div>
                    </div>

                    <div class="item-legenda">
                        <span class="bolinha laranja"><i class="bi bi-dot"></i></span>
                        <div>
                            <span>Pagamentos pendentes</span>
                            <strong>R$ 15.300,00</strong>
                            <small>15,7%</small>
                        </div>
                    </div>
                </div>

                <div class="grafico-barras">
                    <div class="valores-grafico">
<<<<<<< HEAD

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

=======
                        <span>R$ 40.000</span>
                        <span>R$ 30.000</span>
                        <span>R$ 20.000</span>
                        <span>R$ 10.000</span>
                        <span>R$ 0</span>
>>>>>>> 4402300a28224e9b2f3c78d5724e958d7c7cdfd2
                    </div>

                    <div class="barras">
<<<<<<< HEAD

                        <?php foreach ($semanasGrafico as $semana): ?>

                            <?php

                            if ($maiorValor > 0) {
                                $altura = ($semana['valor'] / $maiorValor) * 100;
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

=======
                        <div class="barra-item">
                            <div class="barra"></div>
                            <span>01 a 07 <br> Set</span>
                        </div>

                        <div class="barra-item">
                            <div class="barra"></div>
                            <span>08 a 14 <br> Set</span>
                        </div>

                        <div class="barra-item">
                            <div class="barra"></div>
                            <span>15 a 21 <br> Set</span>
                        </div>

                        <div class="barra-item">
                            <div class="barra"></div>
                            <span>22 a 28 <br> Set</span>
                        </div>

                        <div class="barra-item">
                            <div class="barra"></div>
                            <span>29 a 05 <br> Set</span>
                        </div>
>>>>>>> 4402300a28224e9b2f3c78d5724e958d7c7cdfd2
                    </div>
                </div>
            </section>

        </section>

    </main>
</body>
</html>