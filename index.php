<?php
session_start();
require_once("conexao.php");

if (isset($_SESSION['id'])) {
    header("Location: home.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_last_attempt'] = time();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Requisição inválida.");
    }

    if ($_SESSION['login_attempts'] >= 5) {
        $elapsed = time() - $_SESSION['login_last_attempt'];
        if ($elapsed < 900) { 
            $minutos = ceil((900 - $elapsed) / 60);
            $error = "Muitas tentativas. Aguarde {$minutos} minuto(s).";
        } else {
            $_SESSION['login_attempts'] = 0;
        }
    }

    if (!isset($error)) {
        if (empty($_POST['email']) || empty($_POST['senha'])) {
            $error = "Preencha todos os campos!";
        } else {
            $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
            $senha = trim($_POST['senha']);

            if (!$email) {
                $error = "E-mail ou senha incorretos!";
            } else {
                $stmt = $pdo->prepare("SELECT id, nome, email, senha, bio, dataCadastro FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                $userLogin = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($userLogin && password_verify($senha, $userLogin['senha'])) {
                    $_SESSION['login_attempts'] = 0;
                    session_regenerate_id(true);
                    $_SESSION['id'] = $userLogin['id'];
                    $_SESSION['nome'] = $userLogin['nome'];
                    $_SESSION['email'] = $userLogin['email'];
                    $_SESSION['bio'] = $userLogin['bio'];
                    $_SESSION['dataCadastro'] = $userLogin['dataCadastro'];
                    
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    header("Location: home.php");
                    exit();
                } else {
                    $_SESSION['login_attempts']++;
                    $_SESSION['login_last_attempt'] = time();
                    $error = "E-mail ou senha incorretos!";
                }
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
    <link rel="stylesheet" href="estilos/style-root.css">
    <link rel="stylesheet" href="estilos/style-sweet.css">
    <link rel="stylesheet" href="estilos/style-responsive.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="scripts/eyesScript.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" href="favicon_io/favicon.ico" type="image/x-icon">
    <title>DashNet • Login</title>
</head>

<body>

    <?php if (isset($error)): ?>
    <script>
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: <?php echo json_encode($error); ?>,
        confirmButtonText: 'Voltar'
    });
    </script>
    <?php endif; ?>

    <h1>Dashnet</h1>

    <form action="index.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <h3 id="subtitle">Faça o login ou cadastre-se</h3>

        <div class="form_div">
            <input type="email" name="email" id="input_email" required>
            <label for="email">E-mail</label>
            <img src="https://res.cloudinary.com/dzbdewkbp/image/upload/v1770328078/e-mail_rh3ca6.png" alt="Icone email"
                class="form_icons" id="icon_login">
        </div>

        <div class="form_div">
            <input type="password" name="senha" id="input_senha" oninput="on_pass()" required>
            <label for="senha">Senha</label>
            <img src="https://res.cloudinary.com/dzbdewkbp/image/upload/v1770328097/cadeado_kszx2t.png"
                alt="Icone senha" class="form_icons" id="icon_senha">
            <i class="bi bi-eye-fill" id="btn_senha" onclick="eye_pass()"></i>
        </div>

        <div class="cadastro">
            <p>Não tem uma conta? <a href="cadastro.php" target="_self">Cadastre-se</a></p>
        </div>

        <button type="submit">Entrar</button>
    </form>

    <footer>
        <p>©2026 Dashnet • Gravataí, RS • gmonni20@gmail.com</p>
    </footer>
</body>

</html>