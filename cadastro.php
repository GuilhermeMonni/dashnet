<?php
session_start();
require_once("conexao.php");

//token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // Validação 
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Requisição inválida.");
    }

    $nome = trim($_POST['nome'] ?? '');
    $email_raw = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (empty($nome) || empty($email_raw) || empty($senha)) {
        $erro = "Preencha todos os campos!";
    } elseif (strlen($nome) < 3) {
        $erro = "O nome deve ter no mínimo 3 caracteres!";
    } elseif (strlen($nome) > 100) {
        $erro = "O nome deve ter no máximo 100 caracteres!"; 
    } elseif (!filter_var($email_raw, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido!";
    } elseif (strlen($senha) < 8) {
        $erro = "A senha deve ter no mínimo 8 caracteres!";
    } else {
        $email = filter_var($email_raw, FILTER_VALIDATE_EMAIL);

        // Verifica email
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) { 
            $erro = "Este email já está cadastrado!";
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql_insert = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES(?, ?, ?)");
            $sql_insert->execute([$nome, $email, $hash]);

            if ($sql_insert->rowCount() > 0) {
                $sucess = true;
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } else {
                $erro = "Erro ao cadastrar usuário. Tente novamente.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/style-main.css">
    <link rel="stylesheet" href="estilos/style-sweet.css">
    <link rel="stylesheet" href="estilos/style-root.css">
    <link rel="shortcut icon" href="favicon_io/favicon.ico" type="image/x-icon">
    <script src="scripts/eyesScript.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>DashNet - cadastro</title>
</head>

<body>

    <?php if (isset($sucess)): ?>
    <script>
    Swal.fire({
        icon: 'success',
        title: 'Cadastro realizado!',
        text: 'Sua conta foi criada com sucesso.',
        confirmButtonText: 'Login'
    }).then(() => {
        window.location.href = 'index.php';
    });
    </script>
    <?php endif; ?>

    <?php if (isset($erro)): ?>
    <script>
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: <?php echo json_encode($erro); ?>,
        confirmButtonText: 'Voltar'
    });
    </script>
    <?php endif; ?>

    <a href="index.php"><button id="btn_return"><i class="bi bi-arrow-left-square-fill"></i> Voltar</button></a>
    <h1>Dashnet</h1>

    <form action="cadastro.php" method="POST">
        <!-- Token  -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <h3 id="subtitle">Insira as seguintes informações:</h3>

        <div class="form_div">
            <input type="text" name="nome" maxlength="100" required>
            <label for="nome" class="form_label">Nome</label>
            <img src="https://res.cloudinary.com/dzbdewkbp/image/upload/v1770327839/homem-usuario_diarpq.png"
                alt="Icone login" class="form_icons">
        </div>

        <div class="form_div">
            <input type="email" name="email" required>
            <label for="email" class="form_label">Email</label>
            <img src="https://res.cloudinary.com/dzbdewkbp/image/upload/v1770328078/e-mail_rh3ca6.png"
                alt="Icone de email" class="form_icons">
        </div>

        <div class="form_div">
            <input type="password" name="senha" id="input_senha" oninput="on_pass()" minlength="8" required>
            <label for="senha" class="form_label">Senha</label>
            <img src="https://res.cloudinary.com/dzbdewkbp/image/upload/v1770328097/cadeado_kszx2t.png"
                alt="Icone de senha" class="form_icons" id="icon_senha">
            <i class="bi bi-eye-fill" id="btn_senha" onclick="eye_pass()"></i>
        </div>

        <button type="submit">Finalizar</button>
    </form>

</body>
<footer>
    <img src="imagens/logo-monni.png" alt="Logo">
</footer>

</html>