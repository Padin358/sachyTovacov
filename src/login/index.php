<?php
session_start();

$correct_username = password_hash('admin', PASSWORD_DEFAULT);
$correct_password_hash = password_hash('heslo123', PASSWORD_DEFAULT);

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: admin/');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (password_verify($username, $correct_username) && password_verify($password, $correct_password_hash)) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        header('Location: admin/');
        exit;
    }

    $error = 'Nesprávné přihlašovací jméno nebo heslo.';
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../output.css">
    <link rel="stylesheet" href="../theme.css">
    <link rel="stylesheet" href="../admin-theme.css">
    <title>Šachy Tovačov &raquo; Přihlášení do administrace</title>
</head>
<body class="admin-body">
    <main class="admin-login-shell">
        <section class="admin-login-card">
            <p class="admin-kicker">Administrace klubu</p>
            <h1 class="admin-title">Přihlášení</h1>
            <p class="admin-subtitle">Klidnější, responzivní a typograficky sladěná administrace pro správu novinek, hráčů a mládežnických tabulek.</p>

            <?php if ($error): ?>
                <div class="admin-alert admin-alert--error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="admin-grid" style="margin-top: 1.35rem;">
                <div class="admin-field">
                    <label for="username-input" class="admin-label">Uživatelské jméno</label>
                    <input type="text" name="username" id="username-input" required class="admin-input">
                </div>

                <div class="admin-field">
                    <label for="password-input" class="admin-label">Heslo</label>
                    <div class="admin-file-row">
                        <input type="password" name="password" id="password-input" required class="admin-input">
                        <button type="button" id="toggle-password" class="admin-button admin-button--secondary">Zobrazit heslo</button>
                    </div>
                </div>

                <div class="admin-actions" style="margin-top: 0.35rem;">
                    <button type="submit" class="admin-button admin-button--primary">Přihlásit se</button>
                    <a href="../" class="admin-button admin-button--ghost">Zpět na web</a>
                </div>
            </form>
        </section>
    </main>

    <script>
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password-input');

        togglePassword.addEventListener('click', () => {
            const showPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', showPassword ? 'text' : 'password');
            togglePassword.textContent = showPassword ? 'Skrýt heslo' : 'Zobrazit heslo';
        });
    </script>
</body>
</html>
