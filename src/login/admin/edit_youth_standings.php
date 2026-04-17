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
    <title>Správa mládeže</title>
</head>
<body class="bg-[#0a0a0a] text-[#dfdfdf] min-h-screen p-8">
    <div class="flex justify-center absolute top-5 left-1/2 -translate-x-1/2">
        <div class="border border-red-500/50 text-red-400 px-4 py-3 rounded-lg inline-block">
            Propis změn do tabulky může chvíli trvat.
        </div>
    </div>

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold text-blue-400">Šachy Tovačov &raquo; Správa mládeže</h1>
        <a href="index.php" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg transition-colors font-semibold">Zpět</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <?php $messages = ['meta' => 'Nastavení sekce bylo úspěšně upraveno.', 'upraven' => 'Jména byla úspěšně upravena.']; ?>
        <div class="bg-green-700 text-white p-4 rounded-lg mb-6"><?php echo $messages[$_GET['success']] ?? ''; ?></div>
    <?php endif; ?>

    <div class="bg-[#1d1d1d] p-6 rounded-xl mb-6">
        <h2 class="text-xl font-semibold mb-4">Nastavení sekce</h2>
        <form method="POST" class="grid grid-cols-2 gap-4">
            <input type="hidden" name="action" value="update_meta">
            <div>
                <label class="block mb-1 text-sm text-gray-400">Losovací kolo</label>
                <select name="kolo" class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full">
                    <?php for ($i = 1; $i <= 20; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $metadata['kolo'] === $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm text-gray-400">Datum</label>
                <input type="text" name="datum" value="<?php echo htmlspecialchars($metadata['datum']); ?>" class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full">
            </div>
            <div class="col-span-2">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 px-6 py-2 rounded-lg transition-colors font-semibold cursor-pointer">Uložit nastavení</button>
            </div>
        </form>
    </div>

    <div class="bg-[#1d1d1d] p-6 rounded-xl">
        <h2 class="text-xl font-semibold mb-2">Hlavní tabulka</h2>
        <p class="text-sm text-gray-400 mb-6">Čísla pozic jsou pevná. Tady se upravují pouze jména hráčů.</p>

        <form method="POST" class="space-y-6">
            <input type="hidden" name="action" value="update_names">

            <?php foreach ($skupiny as $skupinaIndex => $skupina): ?>
                <section class="border border-[#333] rounded-xl p-4">
                    <h3 class="text-lg font-semibold mb-4"><?php echo htmlspecialchars($skupina['nazev']); ?></h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <?php foreach ($skupina['hraci'] as $hracIndex => $hrac): ?>
                            <div>
                                <label class="block mb-1 text-sm text-gray-400"><?php echo htmlspecialchars($hrac['cislo']); ?>.</label>
                                <input
                                    type="text"
                                    name="jmena[<?php echo $skupinaIndex; ?>][<?php echo $hracIndex; ?>]"
                                    value="<?php echo htmlspecialchars($hrac['jmeno']); ?>"
                                    class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full"
                                >
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>

            <button type="submit" class="bg-blue-500 hover:bg-blue-600 px-6 py-2 rounded-lg transition-colors font-semibold cursor-pointer">Uložit jména</button>
        </form>
    </div>
</body>
</html>
