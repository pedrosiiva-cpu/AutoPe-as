<?php
require_once __DIR__ . '/auth.php';
exigirLogin();
$usuarios = carregarUsuarios();
$usuario = (string) $_SESSION['usuario'];
$erro = '';
$sucesso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $atual = (string) ($_POST['senha_atual'] ?? '');
    $nova = (string) ($_POST['nova_senha'] ?? '');
    $confirmacao = (string) ($_POST['confirma_senha'] ?? '');
    $armazenada = $usuarios[$usuario] ?? '';
    $valida = password_get_info($armazenada)['algo'] !== null ? password_verify($atual, $armazenada) : hash_equals($armazenada, $atual);
    if (!$valida) $erro = 'A senha atual está incorreta.';
    elseif (strlen($nova) < 4) $erro = 'A nova senha deve possuir pelo menos 4 caracteres.';
    elseif ($nova !== $confirmacao) $erro = 'A confirmação não coincide com a nova senha.';
    elseif ($nova === $atual) $erro = 'A nova senha deve ser diferente da senha atual.';
    else {
        $usuarios[$usuario] = password_hash($nova, PASSWORD_DEFAULT);
        if (salvarUsuarios($usuarios)) {
            $_SESSION = [];
            session_destroy();
            $sucesso = 'Senha alterada. Faça login novamente.';
        } else $erro = 'Não foi possível salvar a senha.';
    }
}
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Alterar senha | AutoPeças</title><link rel="stylesheet" href="css/login.css"><link rel="stylesheet" href="css/sidebar.css"></head><body>
<div class="app" style="display:flex;min-height:100vh;align-items:center"><?php require __DIR__ . '/sidebar.php'; ?><main class="caixa caixa-edita" style="flex:1"><section class="direita"><div class="formulario"><h2>Alterar senha</h2><p class="subtitulo">Usuário: <?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?></p>
<?php if ($erro !== ''): ?><p class="alerta-erro"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<?php if ($sucesso !== ''): ?><p class="alerta-sucesso"><?= htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8') ?> <a href="login.php">Ir para login</a></p><?php else: ?>
<form method="post"><div class="grupo"><label for="senha_atual">Senha atual</label><input id="senha_atual" name="senha_atual" type="password" required></div><div class="grupo"><label for="nova_senha">Nova senha</label><input id="nova_senha" name="nova_senha" type="password" minlength="4" required></div><div class="grupo"><label for="confirma_senha">Confirmar nova senha</label><input id="confirma_senha" name="confirma_senha" type="password" minlength="4" required></div><button class="botao" type="submit">SALVAR NOVA SENHA</button></form><?php endif; ?><p><a href="funcionarios.php">Voltar ao sistema</a></p></div></section></main></div></body></html>
