<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Autopeças - Gestão de Pagamentos</title>

    <link rel="stylesheet" href="css/pagamentos.css">

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
    >
</head>

<body>

    <div class="app">

        <aside class="sidebar">

            <div class="brand">

                <div class="brand-icon">
                    <span class="material-symbols-outlined">
                        directions_car
                    </span>
                </div>

                <div class="brand-name">
                    AUTOPEÇAS
                </div>

                <div class="brand-sub">
                    GESTÃO DE PAGAMENTOS
                </div>

            </div>


            <nav class="nav-menu">

                <a href="dashboard.php" class="nav-link">
                    <span class="material-symbols-outlined">
                        home
                    </span>
                    <span>Dashboard</span>
                </a>


                <a href="funcionarios.php" class="nav-link">
                    <span class="material-symbols-outlined">
                        groups
                    </span>
                    <span>Funcionários</span>
                </a>


                <a href="pagamentos.php" class="nav-link">
                    <span class="material-symbols-outlined">
                        payments
                    </span>
                    <span>Pagamentos</span>
                </a>


                <a href="prazos_alertas.php" class="nav-link">
                    <span class="material-symbols-outlined">
                        notifications
                    </span>
                    <span>Prazos e Alertas</span>
                </a>


                <a href="relatorios.php" class="nav-link">
                    <span class="material-symbols-outlined">
                        bar_chart
                    </span>
                    <span>Relatórios</span>
                </a>

            </nav>


            <div class="nav-footer">

                <a href="#" class="nav-link logout">
                    <span class="material-symbols-outlined">
                        logout
                    </span>
                    <span>Sair</span>
                </a>

            </div>

        </aside>


        <main class="main">

            <header class="topbar">

                <div class="page-title">

                    <h1>
                        Sistema de Autopeças
                    </h1>

                    <p>
                        Gestão financeira e controle de pagamentos.
                    </p>

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

                        <h2>
                            Bem-vindo ao sistema
                        </h2>

                        <p>
                            Acesse uma das opções abaixo para continuar.
                        </p>

                    </div>

                </div>


                <div class="summary-cards">

                    <a href="dashboard.php" class="summary-card">

                        <div class="summary-icon">
                            <span class="material-symbols-outlined">
                                dashboard
                            </span>
                        </div>

                        <div>
                            <div class="summary-label">
                                Dashboard
                            </div>

                            <div class="summary-description">
                                Visualizar o resumo do sistema
                            </div>
                        </div>

                    </a>


                    <a href="pagamentos.php" class="summary-card">

                        <div class="summary-icon">
                            <span class="material-symbols-outlined">
                                payments
                            </span>
                        </div>

                        <div>
                            <div class="summary-label">
                                Pagamentos
                            </div>

                            <div class="summary-description">
                                Visualizar e registrar pagamentos
                            </div>
                        </div>

                    </a>


                    <a href="prazos_alertas.php" class="summary-card">

                        <div class="summary-icon">
                            <span class="material-symbols-outlined">
                                notifications
                            </span>
                        </div>

                        <div>
                            <div class="summary-label">
                                Prazos e Alertas
                            </div>

                            <div class="summary-description">
                                Acompanhar vencimentos
                            </div>
                        </div>

                    </a>


                    <a href="funcionarios.php" class="summary-card">

                        <div class="summary-icon">
                            <span class="material-symbols-outlined">
                                groups
                            </span>
                        </div>

                        <div>
                            <div class="summary-label">
                                Funcionários
                            </div>

                            <div class="summary-description">
                                Visualizar funcionários
                            </div>
                        </div>

                    </a>

                </div>

            </section>

        </main>

    </div>

</body>

</html>