<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../');
    exit;
}

if (isset($_POST['zaloha'])) {
    $zip = new ZipArchive();
    $zipName = 'zaloha_' . date('Y-m-d') . '.zip';
    $zipPath = '../../' . $zipName;

    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFile('../../data_novinky.json', 'data_novinky.json');
    $zip->addFile('../../data_tabulka.json', 'data_tabulka.json');
    $zip->addFile('../../data_mladez.json', 'data_mladez.json');
    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipName . '"');
    header('Content-Length: ' . filesize($zipPath));
    readfile($zipPath);
    unlink($zipPath);
    exit;
}

$obnova_error = null;
$obnova_success = false;

if (isset($_POST['obnova']) && !empty($_FILES['zaloha_zip']['name'])) {
    $allowed_zip = ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'];

    if (!in_array($_FILES['zaloha_zip']['type'], $allowed_zip) && !str_ends_with(strtolower($_FILES['zaloha_zip']['name']), '.zip')) {
        $obnova_error = 'Nahraný soubor není ZIP archiv.';
    } else {
        $zip = new ZipArchive();
        if ($zip->open($_FILES['zaloha_zip']['tmp_name']) === true) {
            $ocekavane = ['data_novinky.json', 'data_tabulka.json', 'data_mladez.json'];
            $nalezene = [];

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $nalezene[] = $zip->getNameIndex($i);
            }

            $chybejici = array_diff($ocekavane, $nalezene);
            if (!empty($chybejici)) {
                $obnova_error = 'ZIP neobsahuje: ' . implode(', ', $chybejici);
            } else {
                foreach ($ocekavane as $soubor) {
                    $obsah = $zip->getFromName($soubor);
                    if (json_decode($obsah) === null) {
                        $obnova_error = $soubor . ' obsahuje neplatný JSON.';
                        break;
                    }
                    file_put_contents('../../' . $soubor, $obsah);
                }

                if (!$obnova_error) {
                    $obnova_success = true;
                }
            }
            $zip->close();
        } else {
            $obnova_error = 'ZIP archiv se nepodařilo otevřít.';
        }
    }
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
<div class="flex justify-center absolute top-5 left-1/2 -translate-x-1/2">
    <div class="border border-red-500/50 text-red-400 px-4 py-3 rounded-lg inline-block">
        ⚠️ Propis změn do tabulky může chvíli trvat.
    </div>
</div>

<div class="">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold text-blue-400">Šachy Tovačov &raquo; Admin Panel</h1>
        <button class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg transition-colors text-[#dfdfdf] font-semibold js-toggle-popup cursor-pointer">Odhlásit se</button>
    </div>

    <div class="bg-[#1d1d1d] p-6 rounded-xl">
        <h2 class="text-xl font-semibold mb-4">Vítejte, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p>Toto je admin panel. Zde můžete spravovat obsah webu.</p>

        <div class="mt-16">
            <p class="text-xl">Prosím, vyberte co chcete upravit:</p>
            <div class="flex flex-row flex-wrap justify-start gap-10 p-3">
                <a href="edit_standings.php" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg transition-colors text-[#dfdfdf] font-semibold">Tabulka hráčů</a>
                <a href="edit_youth_standings.php" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg transition-colors text-[#dfdfdf] font-semibold">Mládež</a>
                <a href="edit_news.php" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg transition-colors text-[#dfdfdf] font-semibold">Novinky</a>
            </div>
        </div>

        <div class="mt-8 pt-8 border-t border-[#333]">
            <p class="text-xl mb-3">Záloha dat:</p>

            <?php if ($obnova_success): ?>
            <div class="bg-green-700 text-white p-4 rounded-lg mb-4">Záloha byla úspěšně obnovena.</div>
            <?php endif; ?>
            <?php if ($obnova_error): ?>
            <div class="bg-red-700 text-white p-4 rounded-lg mb-4">Chyba: <?php echo htmlspecialchars($obnova_error); ?></div>
            <?php endif; ?>

            <div class="flex flex-row flex-wrap gap-10 items-start">
                <div>
                    <p class="text-sm text-gray-400 mb-2">Stáhnout aktuální zálohu:</p>
                    <form method="POST">
                        <button type="submit" name="zaloha" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg transition-colors text-[#dfdfdf] font-semibold cursor-pointer">
                            ⬇️ Stáhnout zálohu
                        </button>
                    </form>
                </div>

                <div>
                    <p class="text-sm text-gray-400 mb-2">Obnovit ze zálohy:</p>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="flex gap-2 items-center">
                            <input type="file" name="zaloha_zip" accept=".zip" id="zaloha-input" class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg">
                            <button type="button" onclick="document.getElementById('zaloha-input').value = ''" class="bg-[#2d2d2d] hover:bg-[#3d3d3d] px-3 py-2 rounded-lg transition-colors text-sm cursor-pointer whitespace-nowrap">Zrušit výběr</button>
                            <button type="submit" name="obnova" onclick="return confirm('Opravdu chcete obnovit zálohu? Aktuální data budou přepsána!')" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg transition-colors text-[#dfdfdf] font-semibold cursor-pointer whitespace-nowrap">
                                ⬆️ Obnovit zálohu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="fixed top-0 left-0 h-full w-full bg-black/70 hidden transition-all" id="logoutPopup">
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-[#1d1d1d] p-3 rounded-lg flex flex-col justify-center gap-5 items-center">
        <button class="text-red-600 absolute top-1 right-1 cursor-pointer hover:text-red-700 transition-colors js-toggle-popup"><i class="fa-solid fa-x text-xl"></i></button>
        <p class="text-xl m-3">Opravdu se chcete odhlásit?</p>
        <a href="logout.php" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg transition-colors text-[#dfdfdf] font-semibold">Odhlásit</a>
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

    logoutPopup.addEventListener("click", (e) => {
        if (e.target === logoutPopup) logoutPopupFunction()
    })

    document.getElementById('zaloha-input').addEventListener('change', () => {
        const file = document.getElementById('zaloha-input').files[0]
        if (file && file.type !== 'application/zip' && !file.name.endsWith('.zip')) {
            alert('Lze nahrát pouze ZIP archiv.')
            document.getElementById('zaloha-input').value = ''
        }
    })
</script>
</body>
</html>
