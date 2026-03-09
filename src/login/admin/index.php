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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../../output.css">
    <title>Admin Panel</title>
</head>
<body class="bg-[#0a0a0a] text-[#dfdfdf] min-h-screen p-8">
    <div class="">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-semibold text-blue-400">Šachy Tovačov &raquo; Admin Panel</h1>
            <button class="bg-blue-400 hover:bg-blue-500 px-4 py-2 rounded-lg transition-colors text-[#dfdfdf] font-semibold js-toggle-popup cursor-pointer">Odhlásit se</button>
        </div>
        
        <div class="bg-[#1d1d1d] p-6 rounded-xl">
            <h2 class="text-xl font-semibold mb-4">Vítejte, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p>Toto je admin panel. Zde můžete spravovat obsah webu.</p>
        </div>
    </div>
    <div class="fixed top-0 left-0 h-full w-full bg-black/70 hidden transition-all" id="logoutPopup">
        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-[#1d1d1d] p-3 rounded-lg flex flex-col justify-center gap-5 items-center">
            <button class="text-red-600 absolute top-1 right-1 cursor-pointer hover:text-red-700 transition-colors js-toggle-popup"><i class="fa-solid fa-x text-xl"></i></button>
            <p class="text-xl m-3">Opravdu se chcete odhlásit?</p>
            <a href="logout.php" class="bg-blue-400 hover:bg-blue-500 px-4 py-2 rounded-lg transition-colors text-[#dfdfdf] font-semibold">Odhlásit</a>
        </div>
    </div>
    <script>
        const toggleButtons = document.querySelectorAll(".js-toggle-popup")
        const logoutPopup = document.getElementById("logoutPopup")

        const logoutPopupFunction = () => {
            logoutPopup.classList.toggle("hidden")
        }

        toggleButtons.forEach(button => {
            button.addEventListener("click", logoutPopupFunction)
        })
    </script>
</body>
</html>