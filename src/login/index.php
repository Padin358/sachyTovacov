<?php
session_start();

// Hard-coded credentials
$correct_username = 'admin';
$correct_password_hash = password_hash('heslo123', PASSWORD_DEFAULT); // Změň heslo!

// Pokud je už přihlášený, přesměruj do admin panelu
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: admin/');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Ověření přihlašovacích údajů
    if ($username === $correct_username && password_verify($password, $correct_password_hash)) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        header('Location: admin/');
        exit;
    } else {
        $error = 'Nesprávné přihlašovací jméno nebo heslo!';
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../output.css">
    <title>Šachy Tovačov &raquo; Úvod</title>
</head>
<body class="bg-[#0a0a0a] text-[#dfdfdf] min-h-screen flex flex-col justify-center items-center">
    <h1 class="text-3xl font-semibold text-center mt-8">Šachy Tovačov &raquo; Admin Panel</h1>
    <section class="w-lg h-3xl bg-[#1d1d1d] mt-12 p-3 rounded-xl">
        <h2 class="font-semibold text-xl">Přihlášení</h2>
        
        <?php if ($error): ?>
            <div class="bg-red-900 text-red-200 p-3 rounded-md mt-3">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="flex flex-col justify-center gap-5 p-3">
            <div class="flex flex-row flex-wrap justify-evenly gap-5">
                <p class="">Přihlašovací jméno:</p>
                <input type="text" name="username" id="username-input" required class="cursor-pointer bg-[#2d2d2d] p-1 rounded-md hover:bg-[#393939] transition-colors duration-all flex-grow">
            </div>
            <div class="flex flex-row flex-wrap justify-evenly gap-5 relative">
                <p class="">Heslo:</p>
                <input type="password" name="password" id="password-input" required class="cursor-pointer bg-[#2d2d2d] p-1 pr-10 rounded-md hover:bg-[#393939] transition-colors duration-all flex-grow">
                <button type="button" id="toggle-password" class="absolute right-2 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 transition-colors cursor-pointer">
                    <svg id="eye-closed" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                    </svg>
                    <svg id="eye-open" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </div>
            <input type="submit" value="Přihlásit se" class="text-lg cursor-pointer bg-[#2d2d2d] p-2 rounded-lg hover:bg-[#393939] transition-colors duration-all">
        </form>
    </section>
<script>
    const togglePassword = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password-input');
    const eyeClosed = document.getElementById('eye-closed');
    const eyeOpen = document.getElementById('eye-open');
    
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        eyeClosed.classList.toggle('hidden');
        eyeOpen.classList.toggle('hidden');
    });
</script>
</body>
</html>