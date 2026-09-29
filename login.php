


<?php

$erro = "";

$arquivoUsuarios = __DIR__ . "/usuarios.json";
$usuarios = ["admin" => "123456"];
if (file_exists($arquivoUsuarios)) {
    $dados = json_decode(file_get_contents($arquivoUsuarios), true);
    if (is_array($dados)) {
        $usuarios = $dados;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario = trim($_POST["usuario"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if (isset($usuarios[$usuario]) && $usuarios[$usuario] === $senha) {

        header("Location: funcionarios.html");
        exit;

    } else {

        $erro = "Usuário ou senha incorretos.";

    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AutoPeças Login</title>

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

    <div class="caixa">

        <div class="esquerda">

            <img
                src="./imagens/ChatGPT Image 10 de set. de 2026, 14_21_28.png"
                alt="Banner AutoPeças"
                class="imagem-banner"
            >

        </div>

        <div class="direita">

            <div class="formulario">

                <h2>Bem-vindo!</h2>

                <p class="subtitulo">
                    Acesse o sistema para continuar
                </p>

                <?php if ($erro != ""): ?>

                    <p style="color: #ef4444; background: #fef2f2; border: 1px solid #fecaca; padding: 10px 14px; border-radius: 6px; margin-bottom: 1.5rem; font-size: 0.9rem;">
                        <?= htmlspecialchars($erro) ?>
                    </p>

                <?php endif; ?>

                <form action="login.php" method="POST">

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
                                value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                                required
                            >

                        </div>

                    </div>

                    <div class="grupo">

                        <label for="senha">
                            Senha
                        </label>

                        <div class="campo">

                            <i class="fa-solid fa-lock"></i>

                            <input
                                type="password"
                                id="senha"
                                name="senha"
                                placeholder="Digite sua senha"
                                required
                            >

                            <i class="fa-regular fa-eye olho" onclick="toggleSenha('senha', this)"></i>

                        </div>

                    </div>

                    <div class="acoes">

                        <label class="lembrar">

                            <input
                                type="checkbox"
                                name="lembrar"
                            >

                            <span class="marcador"></span>

                            Lembrar-me

                        </label>

                        <a href="editaSenha.php" class="esqueceu">
                            Esqueceu sua senha?
                        </a>

                    </div>

                    <button type="submit" class="botao">
                        ENTRAR
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