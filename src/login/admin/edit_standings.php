<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../');
    exit;
}

$file = '../../data_tabulka.json';
$data = json_decode(file_get_contents($file), true);

// Uložení upraveného hráče
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'update') {
        $index = (int)$_POST['index'];
        $data[$index]['jmeno'] = $_POST['jmeno'];
        $data[$index]['datum_narozeni'] = $_POST['datum_narozeni'];
        $data[$index]['vt'] = (int)$_POST['vt'];
        $data[$index]['c_lok'] = (int)$_POST['c_lok'];
        $data[$index]['elo_cr'] = (int)$_POST['elo_cr'];
        $data[$index]['elo_rapid'] = (int)$_POST['elo_rapid'];
        $data[$index]['elo_fide'] = (int)$_POST['elo_fide'];
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: edit_standings.php?success=upraven');
        exit;
    }

    if ($_POST['action'] === 'add') {
        $data[] = [
            'jmeno' => $_POST['jmeno'],
            'datum_narozeni' => $_POST['datum_narozeni'],
            'vt' => (int)$_POST['vt'],
            'c_lok' => (int)$_POST['c_lok'],
            'elo_cr' => (int)$_POST['elo_cr'],
            'elo_rapid' => (int)$_POST['elo_rapid'],
            'elo_fide' => (int)$_POST['elo_fide'],
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
    <title>Správa hráčů</title>
</head>
<body class="bg-[#0a0a0a] text-[#dfdfdf] min-h-screen p-8">
    <!--  Varování  -->
    <div class="flex justify-center absolute top-5 left-1/2 -translate-x-1/2">
        <div class="border border-red-500/50 text-red-400 px-4 py-3 rounded-lg inline-block">
            ⚠️ Propis změn do tabulky může chvíli trvat.
        </div>
    </div>

    <!--  Content  -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold text-blue-400">Šachy Tovačov &raquo; Správa hráčů</h1>
        <a href="index.php" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg transition-colors font-semibold">Zpět</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <?php $messages = ['upraven' => 'Hráč byl úspěšně upraven.', 'pridan' => 'Hráč byl úspěšně přidán.', 'odebran' => 'Hráč byl úspěšně odebrán.']; ?>
        <div class="bg-green-700 text-white p-4 rounded-lg mb-6"><?php echo $messages[$_GET['success']] ?? ''; ?></div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6">
        <button onclick="switchTab('upravit')" id="tab-upravit" class="tab-btn bg-blue-500 hover:bg-blue-600 transition-colors px-4 py-2 rounded-lg font-semibold cursor-pointer">Upravit hráče</button>
        <button onclick="switchTab('pridat')" id="tab-pridat" class="tab-btn bg-[#2d2d2d] hover:bg-[#3d3d3d] px-4 py-2 rounded-lg font-semibold transition-colors cursor-pointer">Přidat hráče</button>
    </div>

    <!-- Upravit hráče -->
    <div id="tab-content-upravit" class="bg-[#1d1d1d] p-6 rounded-xl">
        <label class="block mb-2 font-semibold">Vyber hráče:</label>
        <select id="hracSelect" class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg mb-6 w-full cursor-pointer">
            <?php foreach ($data as $i => $hrac): ?>
                <option value="<?php echo $i; ?>"><?php echo htmlspecialchars($hrac['jmeno']); ?></option>
            <?php endforeach; ?>
        </select>

        <?php foreach ($data as $i => $hrac): ?>
        <form method="POST" class="hrac-form <?php echo $i !== 0 ? 'hidden' : ''; ?>" data-index="<?php echo $i; ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="index" value="<?php echo $i; ?>">
            <div class="grid grid-cols-2 gap-4">
                <?php foreach ($hrac as $key => $value): ?>
                <div>
                    <label class="block mb-1 text-sm text-gray-400"><?php echo $key; ?></label>
                    <input type="text" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($value); ?>"
                        class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full">
                </div>
                <?php endforeach; ?>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 px-6 py-2 rounded-lg transition-colors font-semibold cursor-pointer">Uložit</button>
                <!-- Odebrání hráče -->
                <button type="button" onclick="confirmDelete(<?php echo $i; ?>, '<?php echo htmlspecialchars($hrac['jmeno']); ?>')"
                    class="bg-red-600 hover:bg-red-700 px-6 py-2 rounded-lg transition-colors font-semibold cursor-pointer">Odebrat hráče</button>
            </div>
        </form>
        <?php endforeach; ?>
    </div>

    <!-- Přidat hráče -->
    <div id="tab-content-pridat" class="bg-[#1d1d1d] p-6 rounded-xl hidden">
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="grid grid-cols-2 gap-4">
                <?php foreach (['jmeno' => '', 'datum_narozeni' => '', 'vt' => 0, 'c_lok' => 0, 'elo_cr' => 0, 'elo_rapid' => 0, 'elo_fide' => 0] as $key => $default): ?>
                <div>
                    <label class="block mb-1 text-sm text-gray-400"><?php echo $key; ?></label>
                    <input type="text" name="<?php echo $key; ?>" value="<?php echo $default; ?>"
                        class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full">
                </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="mt-6 bg-blue-500 hover:bg-blue-600 px-6 py-2 rounded-lg transition-colors font-semibold cursor-pointer">Přidat hráče</button>
        </form>
    </div>

    <!-- Popup pro potvrzení odebrání -->
    <div class="fixed top-0 left-0 h-full w-full bg-black/70 hidden" id="deletePopup">
        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-[#1d1d1d] p-6 rounded-lg flex flex-col gap-5 items-center">
            <p class="text-xl">Opravdu chcete odebrat hráče <strong id="deleteJmeno"></strong>?</p>
            <div class="flex gap-3">
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="index" id="deleteIndex">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 px-6 py-2 rounded-lg transition-colors font-semibold cursor-pointer">Odebrat</button>
                </form>
                <button onclick="document.getElementById('deletePopup').classList.add('hidden')"
                    class="bg-[#2d2d2d] hover:bg-[#3d3d3d] px-6 py-2 rounded-lg transition-colors font-semibold cursor-pointer">Zrušit</button>
            </div>
        </div>
    </div>

    <script>
        // Přepínání hráčů
        const select = document.getElementById('hracSelect')
        const forms = document.querySelectorAll('.hrac-form')

        select.addEventListener('change', () => {
            forms.forEach(form => form.classList.add('hidden'))
            document.querySelector(`.hrac-form[data-index="${select.value}"]`).classList.remove('hidden')
        })

        // Tabs
        function switchTab(tab) {
            document.getElementById('tab-content-upravit').classList.add('hidden')
            document.getElementById('tab-content-pridat').classList.add('hidden')
            document.getElementById('tab-content-' + tab).classList.remove('hidden')

            document.getElementById('tab-upravit').className = 'tab-btn px-4 py-2 rounded-lg font-semibold transition-colors cursor-pointer ' + (tab === 'upravit' ? 'bg-blue-500' : 'bg-[#2d2d2d] hover:bg-[#3d3d3d]')
            document.getElementById('tab-pridat').className = 'tab-btn px-4 py-2 rounded-lg font-semibold transition-colors cursor-pointer ' + (tab === 'pridat' ? 'bg-blue-500' : 'bg-[#2d2d2d] hover:bg-[#3d3d3d]')
        }

        // Potvrzení odebrání
        function confirmDelete(index, jmeno) {
            document.getElementById('deleteIndex').value = index
            document.getElementById('deleteJmeno').textContent = jmeno
            document.getElementById('deletePopup').classList.remove('hidden')
        }

        document.getElementById('deletePopup').addEventListener('click', (e) => {
            if (e.target === document.getElementById('deletePopup')) {
                document.getElementById('deletePopup').classList.add('hidden')
            }
        })
    </script>
</body>
</html>