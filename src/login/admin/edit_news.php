<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../');
    exit;
}

$file = '../../data_novinky.json';
$data = json_decode(file_get_contents($file), true);
$kategorie_options = ['Turnaj', 'Úspěch', 'Oznámení', 'Výsledky', 'Jiné'];
$allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'];

function uploadImage($file_input, $uploadDir) {
    global $allowed_types;

    if (empty($_FILES[$file_input]['name'])) {
        return null;
    }

    if (!in_array($_FILES[$file_input]['type'], $allowed_types)) {
        return 'invalid';
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = pathinfo($_FILES[$file_input]['name'], PATHINFO_EXTENSION);
    $filename = uniqid('clanek_') . '.' . $ext;
    move_uploaded_file($_FILES[$file_input]['tmp_name'], $uploadDir . $filename);

    return 'img/novinky/' . $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $uploadDir = '../../img/novinky/';

    if ($_POST['action'] === 'update') {
        $index = (int)$_POST['index'];
        $data[$index]['datum'] = $_POST['datum'];
        $data[$index]['kategorie'] = $_POST['kategorie'];
        $data[$index]['nadpis'] = $_POST['nadpis'];
        $data[$index]['text'] = $_POST['text'];

        if (isset($_POST['odebrat_obrazek'])) {
            $data[$index]['obrazek'] = null;
        } else {
            $obrazek = uploadImage('obrazek', $uploadDir);
            if ($obrazek && $obrazek !== 'invalid') {
                $data[$index]['obrazek'] = $obrazek;
            }
        }

        if (!empty($_FILES['galerie']['name'][0])) {
            if (!isset($data[$index]['galerie'])) {
                $data[$index]['galerie'] = [];
            }

            foreach ($_FILES['galerie']['tmp_name'] as $k => $tmp) {
                if (empty($_FILES['galerie']['name'][$k])) {
                    continue;
                }

                if (!in_array($_FILES['galerie']['type'][$k], $allowed_types)) {
                    continue;
                }

                $ext = pathinfo($_FILES['galerie']['name'][$k], PATHINFO_EXTENSION);
                $filename = uniqid('galerie_') . '.' . $ext;

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                move_uploaded_file($tmp, $uploadDir . $filename);
                $data[$index]['galerie'][] = 'img/novinky/' . $filename;
            }
        }

        if (!empty($_POST['odebrat_galerie'])) {
            $data[$index]['galerie'] = array_values(array_filter(
                $data[$index]['galerie'],
                fn($g) => !in_array($g, $_POST['odebrat_galerie'])
            ));
        }

        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: edit_news.php?success=upraven');
        exit;
    }

    if ($_POST['action'] === 'add') {
        $maxId = 0;
        foreach ($data as $item) {
            if ($item['id'] > $maxId) {
                $maxId = $item['id'];
            }
        }

        $obrazek = uploadImage('obrazek', $uploadDir);
        if ($obrazek === 'invalid') {
            $obrazek = null;
        }

        $galerie = [];
        if (!empty($_FILES['galerie']['name'][0])) {
            foreach ($_FILES['galerie']['tmp_name'] as $k => $tmp) {
                if (empty($_FILES['galerie']['name'][$k])) {
                    continue;
                }

                if (!in_array($_FILES['galerie']['type'][$k], $allowed_types)) {
                    continue;
                }

                $ext = pathinfo($_FILES['galerie']['name'][$k], PATHINFO_EXTENSION);
                $filename = uniqid('galerie_') . '.' . $ext;

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                move_uploaded_file($tmp, $uploadDir . $filename);
                $galerie[] = 'img/novinky/' . $filename;
            }
        }

        $data[] = [
            'id' => $maxId + 1,
            'datum' => $_POST['datum'],
            'kategorie' => $_POST['kategorie'],
            'nadpis' => $_POST['nadpis'],
            'text' => $_POST['text'],
            'obrazek' => $obrazek,
            'galerie' => $galerie,
        ];

        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: edit_news.php?success=pridan');
        exit;
    }

    if ($_POST['action'] === 'delete') {
        $index = (int)$_POST['index'];
        array_splice($data, $index, 1);
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: edit_news.php?success=odebran');
        exit;
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
    <title>Šachy Tovačov &raquo; Správa novinek</title>
</head>
<body class="admin-body">
    <main class="admin-shell">
        <div class="admin-banner">Poznámka: propis změn na veřejný web může chvíli trvat.</div>

        <header class="admin-header">
            <div>
                <p class="admin-kicker">Administrace</p>
                <h1 class="admin-title">Správa novinek</h1>
                <p class="admin-subtitle">Správa novinek, titulních obrázků a fotogalerií k jednotlivým článkům.</p>
            </div>
            <div class="admin-header__actions">
                <a href="index.php" class="admin-button admin-button--secondary">Zpět na přehled</a>
            </div>
        </header>

        <?php if (isset($_GET['success'])): ?>
            <?php $messages = ['upraven' => 'Článek byl úspěšně upraven.', 'pridan' => 'Článek byl úspěšně přidán.', 'odebran' => 'Článek byl úspěšně odebrán.']; ?>
            <div class="admin-alert admin-alert--success"><?php echo $messages[$_GET['success']] ?? ''; ?></div>
        <?php endif; ?>

        <div class="admin-tabs">
            <button type="button" id="tab-upravit" class="admin-tab is-active" onclick="switchTab('upravit')">Upravit článek</button>
            <button type="button" id="tab-pridat" class="admin-tab" onclick="switchTab('pridat')">Přidat článek</button>
        </div>

        <section id="tab-content-upravit" class="admin-card">
            <?php if (empty($data)): ?>
                <p class="admin-help">Žádné články k dispozici.</p>
            <?php else: ?>
                <div class="admin-select-list">
                    <label for="clanekSelect" class="admin-label">Vyber článek</label>
                    <select id="clanekSelect" class="admin-select">
                        <?php foreach ($data as $i => $clanek): ?>
                            <option value="<?php echo $i; ?>"><?php echo htmlspecialchars($clanek['nadpis']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php foreach ($data as $i => $clanek): ?>
                    <form method="POST" enctype="multipart/form-data" class="clanek-form <?php echo $i !== 0 ? 'hidden' : ''; ?>" data-index="<?php echo $i; ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="index" value="<?php echo $i; ?>">

                        <div class="admin-grid admin-grid--2">
                            <div class="admin-field">
                                <label class="admin-label" for="datum-<?php echo $i; ?>">Datum</label>
                                <input type="text" name="datum" id="datum-<?php echo $i; ?>" value="<?php echo htmlspecialchars($clanek['datum']); ?>" class="admin-input">
                            </div>
                            <div class="admin-field">
                                <label class="admin-label" for="kategorie-<?php echo $i; ?>">Kategorie</label>
                                <select name="kategorie" id="kategorie-<?php echo $i; ?>" class="admin-select">
                                    <?php foreach ($kategorie_options as $kat): ?>
                                        <option value="<?php echo $kat; ?>" <?php echo $clanek['kategorie'] === $kat ? 'selected' : ''; ?>><?php echo $kat; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="admin-field admin-field--full">
                                <label class="admin-label" for="nadpis-<?php echo $i; ?>">Nadpis</label>
                                <input type="text" name="nadpis" id="nadpis-<?php echo $i; ?>" value="<?php echo htmlspecialchars($clanek['nadpis']); ?>" class="admin-input">
                            </div>

                            <div class="admin-field admin-field--full">
                                <label class="admin-label" for="text-<?php echo $i; ?>">Text článku</label>
                                <textarea name="text" id="text-<?php echo $i; ?>" class="admin-textarea"><?php echo htmlspecialchars($clanek['text']); ?></textarea>
                            </div>

                            <div class="admin-field admin-field--full">
                                <label class="admin-label">Hlavní obrázek</label>
                                <?php if (!empty($clanek['obrazek'])): ?>
                                    <div class="admin-thumb-row">
                                        <div class="admin-thumb-preview">
                                            <img src="../../<?php echo htmlspecialchars($clanek['obrazek']); ?>" alt="Hlavní obrázek článku">
                                        </div>
                                        <label class="admin-help" style="display: flex; align-items: center; gap: 0.5rem;">
                                            <input type="checkbox" name="odebrat_obrazek" value="1">
                                            Odebrat hlavní obrázek
                                        </label>
                                    </div>
                                <?php endif; ?>
                                <div class="admin-file-row">
                                    <input type="file" name="obrazek" accept="image/jpeg,image/png,image/webp,image/gif,image/avif" id="obrazek-input-<?php echo $i; ?>" class="admin-file">
                                    <button type="button" class="admin-button admin-button--secondary" onclick="resetInput('obrazek-input-<?php echo $i; ?>')">Zrušit výběr</button>
                                </div>
                            </div>

                            <div class="admin-field admin-field--full">
                                <label class="admin-label">Galerie</label>
                                <?php if (!empty($clanek['galerie'])): ?>
                                    <div class="admin-thumb-grid" style="margin-bottom: 0.75rem;">
                                        <?php foreach ($clanek['galerie'] as $img): ?>
                                            <label class="admin-thumb-toggle">
                                                <img src="../../<?php echo htmlspecialchars($img); ?>" alt="Galerie článku">
                                                <span>
                                                    <input type="checkbox" name="odebrat_galerie[]" value="<?php echo htmlspecialchars($img); ?>">
                                                    Odebrat
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="admin-file-row">
                                    <input type="file" name="galerie[]" accept="image/jpeg,image/png,image/webp,image/gif,image/avif" multiple id="galerie-input-<?php echo $i; ?>" class="admin-file">
                                    <button type="button" class="admin-button admin-button--secondary" onclick="resetInput('galerie-input-<?php echo $i; ?>')">Zrušit výběr</button>
                                </div>
                                <p class="admin-help" style="margin-top: 0.55rem;">Můžete vybrat více souborů najednou.</p>
                            </div>
                        </div>

                        <div class="admin-actions" style="margin-top: 1.25rem;">
                            <button type="submit" class="admin-button admin-button--primary">Uložit změny</button>
                            <button type="button" class="admin-button admin-button--danger" onclick="confirmDelete(<?php echo $i; ?>, '<?php echo htmlspecialchars($clanek['nadpis'], ENT_QUOTES); ?>')">Odebrat článek</button>
                        </div>
                    </form>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <section id="tab-content-pridat" class="admin-card hidden">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">

                <div class="admin-grid admin-grid--2">
                    <div class="admin-field">
                        <label class="admin-label" for="datum-new">Datum</label>
                        <input type="text" name="datum" id="datum-new" placeholder="15. ledna 2026" class="admin-input">
                    </div>
                    <div class="admin-field">
                        <label class="admin-label" for="kategorie-new">Kategorie</label>
                        <select name="kategorie" id="kategorie-new" class="admin-select">
                            <?php foreach ($kategorie_options as $kat): ?>
                                <option value="<?php echo $kat; ?>"><?php echo $kat; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="admin-field admin-field--full">
                        <label class="admin-label" for="nadpis-new">Nadpis</label>
                        <input type="text" name="nadpis" id="nadpis-new" class="admin-input">
                    </div>

                    <div class="admin-field admin-field--full">
                        <label class="admin-label" for="text-new">Text článku</label>
                        <textarea name="text" id="text-new" class="admin-textarea"></textarea>
                    </div>

                    <div class="admin-field admin-field--full">
                        <label class="admin-label">Hlavní obrázek</label>
                        <div class="admin-file-row">
                            <input type="file" name="obrazek" accept="image/jpeg,image/png,image/webp,image/gif,image/avif" id="obrazek-input-new" class="admin-file">
                            <button type="button" class="admin-button admin-button--secondary" onclick="resetInput('obrazek-input-new')">Zrušit výběr</button>
                        </div>
                    </div>

                    <div class="admin-field admin-field--full">
                        <label class="admin-label">Galerie</label>
                        <div class="admin-file-row">
                            <input type="file" name="galerie[]" accept="image/jpeg,image/png,image/webp,image/gif,image/avif" multiple id="galerie-input-new" class="admin-file">
                            <button type="button" class="admin-button admin-button--secondary" onclick="resetInput('galerie-input-new')">Zrušit výběr</button>
                        </div>
                        <p class="admin-help" style="margin-top: 0.55rem;">Můžete vybrat více souborů najednou.</p>
                    </div>
                </div>

                <div class="admin-actions" style="margin-top: 1.25rem;">
                    <button type="submit" class="admin-button admin-button--primary">Přidat článek</button>
                </div>
            </form>
        </section>
    </main>

    <div class="admin-modal hidden" id="deletePopup">
        <div class="admin-modal__dialog">
            <p class="admin-modal__title">Odebrat článek</p>
            <p class="admin-help">Opravdu chcete odebrat článek <strong id="deleteNadpis"></strong>?</p>
            <div class="admin-modal__actions">
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="index" id="deleteIndex">
                    <button type="submit" class="admin-button admin-button--danger">Odebrat</button>
                </form>
                <button type="button" class="admin-button admin-button--secondary" onclick="document.getElementById('deletePopup').classList.add('hidden')">Zrušit</button>
            </div>
        </div>
    </div>

    <script>
        const select = document.getElementById('clanekSelect');
        const forms = document.querySelectorAll('.clanek-form');

        if (select) {
            select.addEventListener('change', () => {
                forms.forEach((form) => form.classList.add('hidden'));
                document.querySelector(`.clanek-form[data-index="${select.value}"]`).classList.remove('hidden');
            });
        }

        function resetInput(id) {
            document.getElementById(id).value = '';
        }

        function switchTab(tab) {
            document.getElementById('tab-content-upravit').classList.toggle('hidden', tab !== 'upravit');
            document.getElementById('tab-content-pridat').classList.toggle('hidden', tab !== 'pridat');
            document.getElementById('tab-upravit').classList.toggle('is-active', tab === 'upravit');
            document.getElementById('tab-pridat').classList.toggle('is-active', tab === 'pridat');
        }

        function confirmDelete(index, nadpis) {
            document.getElementById('deleteIndex').value = index;
            document.getElementById('deleteNadpis').textContent = nadpis;
            document.getElementById('deletePopup').classList.remove('hidden');
        }

        document.getElementById('deletePopup').addEventListener('click', (event) => {
            if (event.target === document.getElementById('deletePopup')) {
                document.getElementById('deletePopup').classList.add('hidden');
            }
        });

        document.querySelectorAll('input[type="file"]').forEach((input) => {
            input.addEventListener('change', () => {
                const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'];
                const files = Array.from(input.files);
                const invalid = files.filter((file) => !allowed.includes(file.type));

                if (invalid.length > 0) {
                    alert(`Nelze nahrát: ${invalid.map((file) => file.name).join(', ')}\nPovolené formáty: JPG, PNG, WEBP, GIF, AVIF`);
                    input.value = '';
                }
            });
        });
    </script>
</body>
</html>
