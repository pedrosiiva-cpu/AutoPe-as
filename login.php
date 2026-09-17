<?php

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario = $_POST["usuario"] ?? "";
    $senha = $_POST["senha"] ?? "";

    // Usuário e senha para teste
    $usuarioCorreto = "admin";
    $senhaCorreta = "123456";

    if ($usuario === $usuarioCorreto && $senha === $senhaCorreta) {

        $sucesso = "Login realizado com sucesso!";

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

                    <p style="color: red;">
                        <?= $erro ?>
                    </p>

                <?php endif; ?>


                <?php if ($sucesso != ""): ?>

                    <p style="color: green;">
                        <?= $sucesso ?>
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

                            <i class="fa-regular fa-eye olho"></i>

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


                        <a href="#" class="esqueceu">
                            Esqueceu sua senha?
                        </a>

                    </div>


                    <button
                        type="submit"
                        class="botao"
                       
                    >
                        ENTRAR
                    </button>

                </form>

            </div>

        </div>

    </div>

</body>

</html>