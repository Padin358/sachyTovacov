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
    <link rel="stylesheet" href="../../output.css">
    <link rel="stylesheet" href="../../theme.css">
    <link rel="stylesheet" href="../../admin-theme.css">
    <title>Šachy Tovačov &raquo; Administrace</title>
</head>
<body class="admin-body">
    <main class="admin-shell">
        <div class="admin-banner">Poznámka: propis změn na veřejný web může chvíli trvat.</div>

        <header class="admin-header">
            <div>
                <p class="admin-kicker">Správa obsahu</p>
                <h1 class="admin-title">Administrace Šachy Tovačov</h1>
                <p class="admin-subtitle">Vítejte, <?php echo htmlspecialchars($_SESSION['username']); ?>. Odtud můžete spravovat články, soupisku i mládežnickou sekci a provádět zálohu dat.</p>
            </div>
            <div class="admin-header__actions">
                <a href="../../" class="admin-button admin-button--ghost">Veřejný web</a>
                <button type="button" class="admin-button admin-button--danger js-toggle-popup">Odhlásit se</button>
            </div>
        </header>

        <section class="admin-card">
            <h2 class="admin-section-title">Co chcete upravit</h2>
            <p class="admin-help">Přístup k úpravám jednotlivých sekcí webu.</p>
            <div class="admin-quicklinks" style="margin-top: 1.25rem;">
                <a href="edit_standings.php" class="admin-quicklink">
                    <p class="admin-quicklink__title">Tabulka hráčů</p>
                    <p class="admin-quicklink__text">Uprava soupisky, ratingů a registračních údajů.</p>
                </a>
                <a href="edit_youth_standings.php" class="admin-quicklink">
                    <p class="admin-quicklink__title">Mládež</p>
                    <p class="admin-quicklink__text">Skupiny, jména hráčů i metadata sekce.</p>
                </a>
                <a href="edit_news.php" class="admin-quicklink">
                    <p class="admin-quicklink__title">Novinky</p>
                    <p class="admin-quicklink__text">Články, hlavní obrázky a fotogalerie.</p>
                </a>
            </div>
        </section>

        <section class="admin-card">
            <h2 class="admin-section-title">Záloha dat</h2>
            <p class="admin-help">Stáhněte si aktuální data nebo obnovte obsah z připraveného ZIP archivu s JSON soubory.</p>

            <?php if ($obnova_success): ?>
                <div class="admin-alert admin-alert--success">Záloha byla úspěšně obnovena.</div>
            <?php endif; ?>

            <?php if ($obnova_error): ?>
                <div class="admin-alert admin-alert--error">Chyba: <?php echo htmlspecialchars($obnova_error); ?></div>
            <?php endif; ?>

            <div class="admin-grid admin-grid--2" style="margin-top: 1.25rem;">
                <div class="admin-record">
                    <p class="admin-label">Stažení</p>
                    <p class="admin-help">Vytvoří se archiv s aktuálními daty novinek, tabulky hráčů a mládeže.</p>
                    <form method="POST" style="margin-top: 1rem;">
                        <button type="submit" name="zaloha" class="admin-button admin-button--primary">Stáhnout zálohu</button>
                    </form>
                </div>

                <div class="admin-record">
                    <p class="admin-label">Obnova</p>
                    <p class="admin-help">Nahrajte ZIP se stejnou strukturou souborů. Původní data budou přepsána.</p>
                    <form method="POST" enctype="multipart/form-data" style="margin-top: 1rem;" class="admin-grid">
                        <div class="admin-field">
                            <label for="zaloha-input" class="admin-label">ZIP archiv</label>
                            <input type="file" name="zaloha_zip" accept=".zip" id="zaloha-input" class="admin-file">
                        </div>
                        <div class="admin-actions">
                            <button type="button" onclick="document.getElementById('zaloha-input').value = ''" class="admin-button admin-button--secondary">Zrušit výběr</button>
                            <button type="submit" name="obnova" onclick="return confirm('Opravdu chcete obnovit zálohu? Aktuální data budou přepsána.')" class="admin-button admin-button--primary">Obnovit zálohu</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <div class="admin-modal hidden" id="logoutPopup">
        <div class="admin-modal__dialog">
            <p class="admin-modal__title">Opravdu se chcete odhlásit?</p>
            <p class="admin-help">Po odhlášení se vrátíte na přihlašovací obrazovku administrace.</p>
            <div class="admin-modal__actions">
                <a href="logout.php" class="admin-button admin-button--danger">Odhlásit</a>
                <button type="button" class="admin-button admin-button--secondary js-toggle-popup">Zůstat přihlášený</button>
            </div>
        </div>
    </div>

    <script>
        const toggleButtons = document.querySelectorAll('.js-toggle-popup');
        const logoutPopup = document.getElementById('logoutPopup');

        const toggleLogoutPopup = () => {
            logoutPopup.classList.toggle('hidden');
        };

        toggleButtons.forEach((button) => {
            button.addEventListener('click', toggleLogoutPopup);
        });

        logoutPopup.addEventListener('click', (event) => {
            if (event.target === logoutPopup) {
                toggleLogoutPopup();
            }
        });

        document.getElementById('zaloha-input').addEventListener('change', () => {
            const file = document.getElementById('zaloha-input').files[0];
            if (file && file.type !== 'application/zip' && !file.name.endsWith('.zip')) {
                alert('Lze nahrát pouze ZIP archiv.');
                document.getElementById('zaloha-input').value = '';
            }
        });
    </script>
</body>
</html>
