<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../');
    exit;
}

$file = '../../data_mladez.json';
$data = json_decode(file_get_contents($file), true);
$metadata = [
    'kolo' => (int)($data['kolo'] ?? 1),
    'datum' => $data['datum'] ?? '',
];
$skupiny = $data['skupiny'] ?? [];
$doprovodny_text = $data['doprovodny_text'] ?? [];
$rozpisy = $data['rozpisy'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_meta') {
        $metadata['kolo'] = max(1, min(20, (int)$_POST['kolo']));
        $metadata['datum'] = $_POST['datum'];

        file_put_contents($file, json_encode([
            'kolo' => $metadata['kolo'],
            'datum' => $metadata['datum'],
            'skupiny' => $skupiny,
            'doprovodny_text' => $doprovodny_text,
            'rozpisy' => $rozpisy,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: edit_youth_standings.php?success=meta');
        exit;
    }

    if ($_POST['action'] === 'update_names') {
        foreach ($skupiny as $skupinaIndex => $skupina) {
            foreach ($skupina['hraci'] as $hracIndex => $hrac) {
                $value = $_POST['jmena'][$skupinaIndex][$hracIndex] ?? $hrac['jmeno'];
                $skupiny[$skupinaIndex]['hraci'][$hracIndex]['jmeno'] = $value;
            }
        }

        file_put_contents($file, json_encode([
            'kolo' => $metadata['kolo'],
            'datum' => $metadata['datum'],
            'skupiny' => $skupiny,
            'doprovodny_text' => $doprovodny_text,
            'rozpisy' => $rozpisy,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: edit_youth_standings.php?success=upraven');
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
    <title>Šachy Tovačov &raquo; Správa mládeže</title>
</head>
<body class="admin-body">
    <main class="admin-shell">
        <div class="admin-banner">Poznámka: změny v mládežnické sekci se na veřejném webu mohou propsat s malým zpožděním.</div>

        <header class="admin-header">
            <div>
                <p class="admin-kicker">Administrace</p>
                <h1 class="admin-title">Správa mládeže</h1>
                <p class="admin-subtitle">Jednotný a klidnější vzhled teď drží i editaci mládežnické části, včetně pohodlné práce na mobilu.</p>
            </div>
            <div class="admin-header__actions">
                <a href="index.php" class="admin-button admin-button--secondary">Zpět na přehled</a>
            </div>
        </header>

        <?php if (isset($_GET['success'])): ?>
            <?php $messages = ['meta' => 'Nastavení sekce bylo úspěšně upraveno.', 'upraven' => 'Jména byla úspěšně upravena.']; ?>
            <div class="admin-alert admin-alert--success"><?php echo $messages[$_GET['success']] ?? ''; ?></div>
        <?php endif; ?>

        <section class="admin-card">
            <h2 class="admin-section-title">Nastavení sekce</h2>
            <p class="admin-help">Tady určíte číslo losovacího kola a datum, které se zobrazí na veřejné stránce tabulek.</p>
            <form method="POST" class="admin-grid admin-grid--2" style="margin-top: 1.2rem;">
                <input type="hidden" name="action" value="update_meta">
                <div class="admin-field">
                    <label class="admin-label" for="kolo">Losovací kolo</label>
                    <select name="kolo" id="kolo" class="admin-select">
                        <?php for ($i = 1; $i <= 20; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $metadata['kolo'] === $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label class="admin-label" for="datum">Datum</label>
                    <input type="text" name="datum" id="datum" value="<?php echo htmlspecialchars($metadata['datum']); ?>" class="admin-input">
                </div>
                <div class="admin-actions">
                    <button type="submit" class="admin-button admin-button--primary">Uložit nastavení</button>
                </div>
            </form>
        </section>

        <section class="admin-card">
            <h2 class="admin-section-title">Hlavní tabulka</h2>
            <p class="admin-help">Čísla pozic jsou pevná. Tady se upravují pouze jména hráčů v jednotlivých skupinách.</p>

            <form method="POST" class="admin-grid" style="margin-top: 1.2rem;">
                <input type="hidden" name="action" value="update_names">

                <?php foreach ($skupiny as $skupinaIndex => $skupina): ?>
                    <section class="admin-record">
                        <h3 class="admin-section-title" style="font-size: 1.25rem;"><?php echo htmlspecialchars($skupina['nazev']); ?></h3>
                        <div class="admin-grid admin-grid--2" style="margin-top: 0.9rem;">
                            <?php foreach ($skupina['hraci'] as $hracIndex => $hrac): ?>
                                <div class="admin-field">
                                    <label class="admin-label" for="jmeno-<?php echo $skupinaIndex . '-' . $hracIndex; ?>"><?php echo htmlspecialchars($hrac['cislo']); ?>.</label>
                                    <input
                                        type="text"
                                        id="jmeno-<?php echo $skupinaIndex . '-' . $hracIndex; ?>"
                                        name="jmena[<?php echo $skupinaIndex; ?>][<?php echo $hracIndex; ?>]"
                                        value="<?php echo htmlspecialchars($hrac['jmeno']); ?>"
                                        class="admin-input"
                                    >
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>

                <div class="admin-actions">
                    <button type="submit" class="admin-button admin-button--primary">Uložit jména</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
