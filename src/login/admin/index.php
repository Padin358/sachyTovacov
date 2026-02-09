<?php
session_start();

// Kontrola, jestli je uživatel přihlášený
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../');
    exit;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../output.css">
    <title>Admin Panel</title>
</head>
<body class="bg-[#0a0a0a] text-[#dfdfdf] min-h-screen p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-semibold">Šachy Tovačov &raquo; Admin Panel</h1>
            <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg transition-colors">
                Odhlásit se
            </a>
        </div>
        
        <div class="bg-[#1d1d1d] p-6 rounded-xl">
            <h2 class="text-xl font-semibold mb-4">Vítejte, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p>Toto je admin panel. Zde můžete spravovat obsah webu.</p>
        </div>
    </div>
</body>
</html>