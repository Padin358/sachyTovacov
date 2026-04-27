<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../');
    exit;
}

$file = '../../data_tabulka.json';
$data = json_decode(file_get_contents($file), true);
$fields = [
    'jmeno' => '',
    'rocnik' => '',
    'elo' => 0,
    'elo_r' => 0,
    'registrace' => '',
];

$fieldLabels = [
    'jmeno' => 'Jméno',
    'rocnik' => 'Ročník',
    'elo' => 'ELO',
    'elo_r' => 'ELO-R',
    'registrace' => 'Registrace',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update') {
        $index = (int)$_POST['index'];
        $data[$index]['jmeno'] = $_POST['jmeno'];
        $data[$index]['rocnik'] = $_POST['rocnik'];
        $data[$index]['elo'] = (int)$_POST['elo'];
        $data[$index]['elo_r'] = (int)$_POST['elo_r'];
        $data[$index]['registrace'] = $_POST['registrace'];

        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: edit_standings.php?success=upraven');
        exit;
    }

    if ($_POST['action'] === 'add') {
        $data[] = [
            'jmeno' => $_POST['jmeno'],
            'rocnik' => $_POST['rocnik'],
            'elo' => (int)$_POST['elo'],
            'elo_r' => (int)$_POST['elo_r'],
            'registrace' => $_POST['registrace'],
        ];

        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: edit_standings.php?success=pridan');
        exit;
    }

    if ($_POST['action'] === 'delete') {
        $index = (int)$_POST['index'];
        array_splice($data, $index, 1);
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: edit_standings.php?success=odebran');
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
    <title>Šachy Tovačov &raquo; Správa hráčů</title>
</head>
<body class="admin-body">
    <main class="admin-shell">
        <div class="admin-banner">Poznámka: propis změn do veřejné tabulky může chvíli trvat.</div>

        <header class="admin-header">
            <div>
                <p class="admin-kicker">Administrace</p>
                <h1 class="admin-title">Správa hráčů</h1>
                <p class="admin-subtitle">Soupiska je nově upravitelná v klidnějším, čitelnějším a plně responzivním rozhraní.</p>
            </div>
            <div class="admin-header__actions">
                <a href="index.php" class="admin-button admin-button--secondary">Zpět na přehled</a>
            </div>
        </header>

        <?php if (isset($_GET['success'])): ?>
            <?php $messages = ['upraven' => 'Hráč byl úspěšně upraven.', 'pridan' => 'Hráč byl úspěšně přidán.', 'odebran' => 'Hráč byl úspěšně odebrán.']; ?>
            <div class="admin-alert admin-alert--success"><?php echo $messages[$_GET['success']] ?? ''; ?></div>
        <?php endif; ?>

        <div class="admin-tabs">
            <button type="button" id="tab-upravit" class="admin-tab is-active" onclick="switchTab('upravit')">Upravit hráče</button>
            <button type="button" id="tab-pridat" class="admin-tab" onclick="switchTab('pridat')">Přidat hráče</button>
        </div>

        <section id="tab-content-upravit" class="admin-card">
            <div class="admin-select-list">
                <label for="hracSelect" class="admin-label">Vyber hráče</label>
                <select id="hracSelect" class="admin-select">
                    <?php foreach ($data as $i => $hrac): ?>
                        <option value="<?php echo $i; ?>"><?php echo htmlspecialchars($hrac['jmeno']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php foreach ($data as $i => $hrac): ?>
                <form method="POST" class="hrac-form <?php echo $i !== 0 ? 'hidden' : ''; ?>" data-index="<?php echo $i; ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="index" value="<?php echo $i; ?>">
                    <div class="admin-grid admin-grid--2">
                        <?php foreach ($fields as $key => $default): ?>
                            <div class="admin-field">
                                <label class="admin-label" for="<?php echo $key . '-' . $i; ?>"><?php echo $fieldLabels[$key]; ?></label>
                                <input
                                    type="text"
                                    id="<?php echo $key . '-' . $i; ?>"
                                    name="<?php echo $key; ?>"
                                    value="<?php echo htmlspecialchars($hrac[$key] ?? $default); ?>"
                                    class="admin-input"
                                >
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="admin-actions" style="margin-top: 1.25rem;">
                        <button type="submit" class="admin-button admin-button--primary">Uložit změny</button>
                        <button type="button" class="admin-button admin-button--danger" onclick="confirmDelete(<?php echo $i; ?>, '<?php echo htmlspecialchars($hrac['jmeno'], ENT_QUOTES); ?>')">Odebrat hráče</button>
                    </div>
                </form>
            <?php endforeach; ?>
        </section>

        <section id="tab-content-pridat" class="admin-card hidden">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="admin-grid admin-grid--2">
                    <?php foreach ($fields as $key => $default): ?>
                        <div class="admin-field">
                            <label class="admin-label" for="<?php echo 'new-' . $key; ?>"><?php echo $fieldLabels[$key]; ?></label>
                            <input
                                type="text"
                                id="<?php echo 'new-' . $key; ?>"
                                name="<?php echo $key; ?>"
                                value="<?php echo htmlspecialchars((string)$default); ?>"
                                class="admin-input"
                            >
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="admin-actions" style="margin-top: 1.25rem;">
                    <button type="submit" class="admin-button admin-button--primary">Přidat hráče</button>
                </div>
            </form>
        </section>
    </main>

    <div class="admin-modal hidden" id="deletePopup">
        <div class="admin-modal__dialog">
            <p class="admin-modal__title">Odebrat hráče</p>
            <p class="admin-help">Opravdu chcete odebrat hráče <strong id="deleteJmeno"></strong>?</p>
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
        const select = document.getElementById('hracSelect');
        const forms = document.querySelectorAll('.hrac-form');

        if (select) {
            select.addEventListener('change', () => {
                forms.forEach((form) => form.classList.add('hidden'));
                document.querySelector(`.hrac-form[data-index="${select.value}"]`).classList.remove('hidden');
            });
        }

        function switchTab(tab) {
            document.getElementById('tab-content-upravit').classList.toggle('hidden', tab !== 'upravit');
            document.getElementById('tab-content-pridat').classList.toggle('hidden', tab !== 'pridat');
            document.getElementById('tab-upravit').classList.toggle('is-active', tab === 'upravit');
            document.getElementById('tab-pridat').classList.toggle('is-active', tab === 'pridat');
        }

        function confirmDelete(index, jmeno) {
            document.getElementById('deleteIndex').value = index;
            document.getElementById('deleteJmeno').textContent = jmeno;
            document.getElementById('deletePopup').classList.remove('hidden');
        }

        document.getElementById('deletePopup').addEventListener('click', (event) => {
            if (event.target === document.getElementById('deletePopup')) {
                document.getElementById('deletePopup').classList.add('hidden');
            }
        });
    </script>
</body>
</html>
