<?php

$erro = "";
$sucesso = "";

$arquivoUsuarios = __DIR__ . "/usuarios.json";
$usuarios = ["admin" => "123456"];

if (file_exists($arquivoUsuarios)) {
    $dados = json_decode(file_get_contents($arquivoUsuarios), true);
    if (is_array($dados)) {
        $usuarios = $dados;
    }
}

$usuario = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

  
    $senhaAtual = $_POST["senha_atual"] ?? "";
    $novaSenha = $_POST["nova_senha"] ?? "";
    $confirmaSenha = $_POST["confirma_senha"] ?? "";

    if (empty($usuario) || empty($senhaAtual) || empty($novaSenha) || empty($confirmaSenha)) {
        $erro = "Por favor, preencha todos os campos.";
    } elseif (!isset($usuarios[$usuario])) {
        $erro = "Usuário não encontrado no sistema.";
    } elseif ($usuarios[$usuario] !== $senhaAtual) {
        $erro = "A senha atual informada está incorreta.";
    } elseif (strlen($novaSenha) < 4) {
        $erro = "A nova senha deve possuir pelo menos 4 caracteres.";
    } elseif ($novaSenha !== $confirmaSenha) {
        $erro = "A nova senha e a confirmação não coincidem.";
    } elseif ($novaSenha === $senhaAtual) {
        $erro = "A nova senha deve ser diferente da senha atual.";
    } else {
        $usuarios[$usuario] = $novaSenha;
        file_put_contents($arquivoUsuarios, json_encode($usuarios, JSON_PRETTY_PRINT));

        $sucesso = "Senha alterada com sucesso! Redirecionando para o login...";
        header("refresh:2;url=login.php");
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AutoPeças - Editar Senha</title>

    <link rel="stylesheet" href="login.css">

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >
</head>

<body>

    <div class="caixa caixa-edita">

        <div class="esquerda">

            <img
                src="./imagens/ChatGPT Image 10 de set. de 2026, 14_21_28.png"
                alt="Banner AutoPeças"
                class="imagem-banner"
            >

        </div>

        <div class="direita">

            <div class="formulario">

                <h2>Editar Senha</h2>

                <p class="subtitulo">
                    Informe os dados abaixo para alterar sua senha
                </p>

                <?php if ($erro != ""): ?>
                    <div class="alerta-erro">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><?= htmlspecialchars($erro) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($sucesso != ""): ?>
                    <div class="alerta-sucesso">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><?= htmlspecialchars($sucesso) ?></span>
                    </div>
                <?php endif; ?>

                <form action="editaSenha.php" method="POST">

                    <div class="grupo">

                        <label for="usuario">
                            Usuário
                        </label>

                        <div class="campo">

                            <i class="fa-regular fa-user"></i>

                            <input
                                type="text"
                                id="usuario"
                                name="usuario"
                                placeholder="Digite seu usuário"
                                value="<?= htmlspecialchars($usuario) ?>"
                                required
                            >

                        </div>

                    </div>

                    

                    </div>

                    <div class="grupo">

                        <label for="nova_senha">
                            Nova Senha
                        </label>

                        <div class="campo">

                            <i class="fa-solid fa-key"></i>

                            <input
                                type="password"
                                id="nova_senha"
                                name="nova_senha"
                                placeholder="Digite a nova senha (mín. 4 caracteres)"
                                required
                            >

                            <i class="fa-regular fa-eye olho" onclick="toggleSenha('nova_senha', this)"></i>

                        </div>

                    </div>

                    <div class="grupo">

                        <label for="confirma_senha">
                            Confirmar Nova Senha
                        </label>

                        <div class="campo">

                            <i class="fa-solid fa-shield-halved"></i>

                            <input
                                type="password"
                                id="confirma_senha"
                                name="confirma_senha"
                                placeholder="Repita a nova senha"
                                required
                            >

                            <i class="fa-regular fa-eye olho" onclick="toggleSenha('confirma_senha', this)"></i>

                        </div>

                    </div>

                    <div class="acoes" style="margin-top: 0.8rem; margin-bottom: 1.5rem;">

                        <a href="login.php" class="link-voltar">
                            <i class="fa-solid fa-arrow-left"></i>
                            Voltar para o Login
                        </a>

                    </div>

                    <button type="submit" class="botao">
                        SALVAR NOVA SENHA
                    </button>

                </form>

            </div>

        </div>

    </div>

    <script>
    function toggleSenha(inputId, icon) {
        const input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
    </script>

</body>

</html>
