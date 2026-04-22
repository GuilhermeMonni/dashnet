<?php
    session_start();
    require_once("conexao.php");

    if (!isset($_SESSION['id'])) {
        header("Location: index.php");
        exit();
    }

    $id = $_SESSION['id'];
    $nome = $_SESSION['nome'];
    $email = $_SESSION['email'];
    $bio = $_SESSION['bio'];
    $dataCadastro = $_SESSION['dataCadastro'];
    $dataCad = new DateTime($dataCadastro);

    try{
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = :id");
        $stmt->execute([':id' => $id]);
        $myPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $profilePosts = json_encode(['success' => true, 'posts' => $myPosts]);
        $countPosts = count($myPosts);
        switch ($countPosts) {
            case 0:
                $qtdPosts = "Nenhum post publicado.";
                break;
            case 1:
                $qtdPosts = (string)$countPosts . " Post publicado.";
                break;
            default:
                $qtdPosts = (string)$countPosts . " Posts publicados.";
                break;
        }
    } catch (PDOException $e){
    error_log("Erro ao buscar os seus posts!" . $e->getMessage());
    http_response_code(500);
    }

    ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/style-home.css">
    <link rel="stylesheet" href="estilos/style-root.css">
    <link rel="stylesheet" href="estilos/style-sweet.css">
    <link rel="stylesheet" href="estilos/style-responsive.css">
    <link rel="stylesheet" href="estilos/style-profile.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="./scripts/homeScript.js"></script>
    <link rel="shortcut icon" href="favicon_io/favicon.ico" type="image/x-icon">
    <title>Meu Perfil • Dashnet</title>
</head>

<body>
    <header class="header-home">
        <div class="logo-container">
            <h1>Dashnet</h1>
        </div>

        <div class="user-info">
            <span class="user-name">
                <i class="bi bi-person-fill user-icon"></i>
                <?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>
            </span>

            <a onclick="home()" class="btn-home">
                <i class="bi bi-house-door-fill"></i> Home
            </a>

            <button type="button" class="btn-logout" onclick="logout()">
                <i class="bi bi-box-arrow-right"></i> Sair
            </button>
        </div>
    </header>

    <main class="profile-page">
        <section class="profile-card">
            <div class="profile-top">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($nome, 0, 1)); ?>
                </div>

                <div class="profile-main-info">
                    <h2><?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p class="profile-bio"><?php echo htmlspecialchars($bio, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>

            <div class="profile-grid">
                <div class="profile-box">
                    <h3>Informações pessoais</h3>
                    <p><i class="bi bi-envelope-fill"></i> <?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <p><i class="bi bi-calendar-check-fill"></i> Membro desde <?php echo $dataCad->format('d/m/Y'); ?>
                    </p>
                </div>

                <div class="profile-box">
                    <h3>Atividade</h3>
                    <p><i class="bi bi-pencil-square"></i> <?php echo $qtdPosts; ?></p>
                    <p><i class="bi bi-person-badge-fill"></i> Perfil ativo</p>
                </div>

                <div class="profile-box">
                    <h3>Ações</h3>
                    <a href="#" class="profile-action-btn"><i class="bi bi-person-gear"></i> Editar perfil</a>
                    <a onclick="home()" class="profile-action-btn secondary"><i class="bi bi-arrow-left-circle"></i>
                        Voltar ao feed</a>
                </div>
            </div>
        </section>
        <section class="feed" id="feed">
            <div id="posts-container">
            </div>
        </section>
    </main>
</body>

</html>