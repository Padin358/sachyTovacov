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
    if (empty($_FILES[$file_input]['name'])) return null;
    if (!in_array($_FILES[$file_input]['type'], $allowed_types)) return 'invalid';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
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
            if ($obrazek && $obrazek !== 'invalid') $data[$index]['obrazek'] = $obrazek;
        }

        if (!empty($_FILES['galerie']['name'][0])) {
            if (!isset($data[$index]['galerie'])) $data[$index]['galerie'] = [];
            foreach ($_FILES['galerie']['tmp_name'] as $k => $tmp) {
if (empty($_FILES['galerie']['name'][$k])) continue;
if (!in_array($_FILES['galerie']['type'][$k], $allowed_types)) continue;
$ext = pathinfo($_FILES['galerie']['name'][$k], PATHINFO_EXTENSION);
$filename = uniqid('galerie_') . '.' . $ext;
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
move_uploaded_file($tmp, $uploadDir . $filename);
$data[$index]['galerie'][] = 'img/novinky/' . $filename;
}
}

if (!empty($_POST['odebrat_galerie'])) {
$data[$index]['galerie'] = array_values(
array_filter($data[$index]['galerie'], fn($g) => !in_array($g, $_POST['odebrat_galerie']))
);
}

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
header('Location: edit_news.php?success=upraven');
exit;
}

if ($_POST['action'] === 'add') {
$maxId = 0;
foreach ($data as $item) {
if ($item['id'] > $maxId) $maxId = $item['id'];
}

$obrazek = uploadImage('obrazek', $uploadDir);
if ($obrazek === 'invalid') $obrazek = null;

$galerie = [];
if (!empty($_FILES['galerie']['name'][0])) {
foreach ($_FILES['galerie']['tmp_name'] as $k => $tmp) {
if (empty($_FILES['galerie']['name'][$k])) continue;
if (!in_array($_FILES['galerie']['type'][$k], $allowed_types)) continue;
$ext = pathinfo($_FILES['galerie']['name'][$k], PATHINFO_EXTENSION);
$filename = uniqid('galerie_') . '.' . $ext;
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
move_uploaded_file($tmp, $uploadDir . $filename);
$galerie[] = 'img/novinky/' . $filename;
}
}

$data[] = [
'id'        => $maxId + 1,
'datum'     => $_POST['datum'],
'kategorie' => $_POST['kategorie'],
'nadpis'    => $_POST['nadpis'],
'text'      => $_POST['text'],
'obrazek'   => $obrazek,
'galerie'   => $galerie,
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
    <title>Správa novinek</title>
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
    <h1 class="text-3xl font-semibold text-blue-400">Šachy Tovačov &raquo; Správa novinek</h1>
    <a href="index.php" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg transition-colors text-[#dfdfdf] font-semibold cursor-pointer">Zpět</a>
</div>

<?php if (isset($_GET['success'])): ?>
<?php $messages = ['upraven' => 'Článek byl úspěšně upraven.', 'pridan' => 'Článek byl úspěšně přidán.', 'odebran' => 'Článek byl úspěšně odebrán.']; ?>
<div class="bg-green-700 text-white p-4 rounded-lg mb-6"><?php echo $messages[$_GET['success']] ?? ''; ?></div>
<?php endif; ?>

<div class="flex gap-2 mb-6">
    <button onclick="switchTab('upravit')" id="tab-upravit" class="tab-btn bg-blue-500 px-4 py-2 rounded-lg font-semibold transition-colors cursor-pointer">Upravit článek</button>
    <button onclick="switchTab('pridat')" id="tab-pridat" class="tab-btn bg-[#2d2d2d] hover:bg-[#3d3d3d] px-4 py-2 rounded-lg font-semibold transition-colors cursor-pointer">Přidat článek</button>
</div>

<!-- Upravit článek -->
<div id="tab-content-upravit" class="bg-[#1d1d1d] p-6 rounded-xl">
    <?php if (empty($data)): ?>
    <p class="text-gray-400">Žádné články k dispozici.</p>
    <?php else: ?>
    <label class="block mb-2 font-semibold">Vyber článek:</label>
    <select id="clanekSelect" class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg mb-6 w-full">
        <?php foreach ($data as $i => $clanek): ?>
        <option value="<?php echo $i; ?>"><?php echo htmlspecialchars($clanek['nadpis']); ?></option>
        <?php endforeach; ?>
    </select>

    <?php foreach ($data as $i => $clanek): ?>
    <form method="POST" enctype="multipart/form-data" class="clanek-form <?php echo $i !== 0 ? 'hidden' : ''; ?>" data-index="<?php echo $i; ?>">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="index" value="<?php echo $i; ?>">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block mb-1 text-sm text-gray-400">Datum</label>
                <input type="text" name="datum" value="<?php echo htmlspecialchars($clanek['datum']); ?>"
                       class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full">
            </div>
            <div>
                <label class="block mb-1 text-sm text-gray-400">Kategorie</label>
                <select name="kategorie" class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full">
                    <?php foreach ($kategorie_options as $kat): ?>
                    <option value="<?php echo $kat; ?>" <?php echo $clanek['kategorie'] === $kat ? 'selected' : ''; ?>><?php echo $kat; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-span-2">
                <label class="block mb-1 text-sm text-gray-400">Nadpis</label>
                <input type="text" name="nadpis" value="<?php echo htmlspecialchars($clanek['nadpis']); ?>"
                       class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full">
            </div>
            <div class="col-span-2">
                <label class="block mb-1 text-sm text-gray-400">Text článku</label>
                <textarea name="text" rows="8" class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full"><?php echo htmlspecialchars($clanek['text']); ?></textarea>
            </div>

            <!-- Hlavní obrázek -->
            <div class="col-span-2">
                <label class="block mb-1 text-sm text-gray-400">Hlavní obrázek</label>
                <?php if (!empty($clanek['obrazek'])): ?>
                <div class="flex items-center gap-4 mb-2">
                    <img src="../../<?php echo htmlspecialchars($clanek['obrazek']); ?>" class="w-32 h-20 object-cover rounded-lg">
                    <label class="flex items-center gap-2 text-sm text-red-400 cursor-pointer">
                        <input type="checkbox" name="odebrat_obrazek" value="1" class="accent-red-500">
                        Odebrat obrázek
                    </label>
                </div>
                <?php endif; ?>
                <div class="flex gap-2 items-center">
                    <input type="file" name="obrazek" accept="image/jpeg,image/png,image/webp,image/gif,image/avif"
                           id="obrazek-input-<?php echo $i; ?>"
                           class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full">
                    <button type="button" onclick="resetInput('obrazek-input-<?php echo $i; ?>')"
                            class="bg-[#2d2d2d] hover:bg-[#3d3d3d] px-3 py-2 rounded-lg transition-colors text-sm cursor-pointer whitespace-nowrap">Zrušit výběr</button>
                </div>
            </div>

            <!-- Galerie -->
            <div class="col-span-2">
                <label class="block mb-1 text-sm text-gray-400">Galerie</label>
                <?php if (!empty($clanek['galerie'])): ?>
                <div class="grid grid-cols-4 gap-2 mb-3">
                    <?php foreach ($clanek['galerie'] as $img): ?>
                    <label class="relative cursor-pointer group">
                        <input type="checkbox" name="odebrat_galerie[]" value="<?php echo htmlspecialchars($img); ?>" class="hidden peer">
                        <img src="../../<?php echo htmlspecialchars($img); ?>" class="w-full h-20 object-cover rounded-lg peer-checked:opacity-40 peer-checked:ring-2 peer-checked:ring-red-500 group-hover:opacity-80 transition-all">
                        <span class="absolute inset-0 flex items-center justify-center text-red-400 text-xs font-semibold opacity-0 peer-checked:opacity-100">Odebrat</span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <p class="text-xs text-gray-500 mb-2">Kliknutím na obrázek ho označíš k odebrání.</p>
                <?php endif; ?>
                <div class="flex gap-2 items-center">
                    <input type="file" name="galerie[]" accept="image/jpeg,image/png,image/webp,image/gif,image/avif" multiple
                           id="galerie-input-<?php echo $i; ?>"
                           class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full">
                    <button type="button" onclick="resetInput('galerie-input-<?php echo $i; ?>')"
                            class="bg-[#2d2d2d] hover:bg-[#3d3d3d] px-3 py-2 rounded-lg transition-colors text-sm cursor-pointer whitespace-nowrap">Zrušit výběr</button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Můžeš vybrat více souborů najednou.</p>
            </div>
        </div>
        <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg transition-colors text-[#dfdfdf] font-semibold cursor-pointer">Uložit</button>
            <button type="button" onclick="confirmDelete(<?php echo $i; ?>, '<?php echo htmlspecialchars($clanek['nadpis'], ENT_QUOTES); ?>')"
                    class="bg-red-600 hover:bg-red-700 px-6 py-2 rounded-lg transition-colors font-semibold cursor-pointer">Odebrat článek</button>
        </div>
    </form>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Přidat článek -->
<div id="tab-content-pridat" class="bg-[#1d1d1d] p-6 rounded-xl hidden">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block mb-1 text-sm text-gray-400">Datum</label>
                <input type="text" name="datum" placeholder="15. ledna 2026"
                       class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full">
            </div>
            <div>
                <label class="block mb-1 text-sm text-gray-400">Kategorie</label>
                <select name="kategorie" class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full">
                    <?php foreach ($kategorie_options as $kat): ?>
                    <option value="<?php echo $kat; ?>"><?php echo $kat; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-span-2">
                <label class="block mb-1 text-sm text-gray-400">Nadpis</label>
                <input type="text" name="nadpis" class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full">
            </div>
            <div class="col-span-2">
                <label class="block mb-1 text-sm text-gray-400">Text článku</label>
                <textarea name="text" rows="8" class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full"></textarea>
            </div>
            <div class="col-span-2">
                <label class="block mb-1 text-sm text-gray-400">Hlavní obrázek (volitelné)</label>
                <div class="flex gap-2 items-center">
                    <input type="file" name="obrazek" accept="image/jpeg,image/png,image/webp,image/gif,image/avif"
                           id="obrazek-input-new"
                           class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full">
                    <button type="button" onclick="resetInput('obrazek-input-new')"
                            class="bg-[#2d2d2d] hover:bg-[#3d3d3d] px-3 py-2 rounded-lg transition-colors text-sm cursor-pointer whitespace-nowrap">Zrušit výběr</button>
                </div>
            </div>
            <div class="col-span-2">
                <label class="block mb-1 text-sm text-gray-400">Galerie (volitelné)</label>
                <div class="flex gap-2 items-center">
                    <input type="file" name="galerie[]" accept="image/jpeg,image/png,image/webp,image/gif,image/avif" multiple
                           id="galerie-input-new"
                           class="bg-[#2d2d2d] text-[#dfdfdf] px-4 py-2 rounded-lg w-full">
                    <button type="button" onclick="resetInput('galerie-input-new')"
                            class="bg-[#2d2d2d] hover:bg-[#3d3d3d] px-3 py-2 rounded-lg transition-colors text-sm cursor-pointer whitespace-nowrap">Zrušit výběr</button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Můžeš vybrat více souborů najednou.</p>
            </div>
        </div>
        <button type="submit" class="mt-6 bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg transition-colors text-[#dfdfdf] font-semibold cursor-pointer">Přidat článek</button>
    </form>
</div>

<!-- Popup pro potvrzení odebrání -->
<div class="fixed top-0 left-0 h-full w-full bg-black/70 hidden" id="deletePopup">
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-[#1d1d1d] p-6 rounded-lg flex flex-col gap-5 items-center">
        <p class="text-xl">Opravdu chcete odebrat článek <strong id="deleteNadpis"></strong>?</p>
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
    const select = document.getElementById('clanekSelect')
    const forms = document.querySelectorAll('.clanek-form')

    if (select) {
        select.addEventListener('change', () => {
            forms.forEach(form => form.classList.add('hidden'))
            document.querySelector(`.clanek-form[data-index="${select.value}"]`).classList.remove('hidden')
        })
    }

    function resetInput(id) {
        document.getElementById(id).value = ''
    }

    function switchTab(tab) {
        document.getElementById('tab-content-upravit').classList.add('hidden')
        document.getElementById('tab-content-pridat').classList.add('hidden')
        document.getElementById('tab-content-' + tab).classList.remove('hidden')
        document.getElementById('tab-upravit').className = 'tab-btn px-4 py-2 rounded-lg font-semibold transition-colors cursor-pointer ' + (tab === 'upravit' ? 'bg-blue-500 hover:bg-blue-600 text-[#dfdfdf]' : 'bg-[#2d2d2d] hover:bg-[#3d3d3d]')
        document.getElementById('tab-pridat').className = 'tab-btn px-4 py-2 rounded-lg font-semibold transition-colors cursor-pointer ' + (tab === 'pridat' ? 'bg-blue-500 hover:bg-blue-600 text-[#dfdfdf]' : 'bg-[#2d2d2d] hover:bg-[#3d3d3d]')
    }

    function confirmDelete(index, nadpis) {
        document.getElementById('deleteIndex').value = index
        document.getElementById('deleteNadpis').textContent = nadpis
        document.getElementById('deletePopup').classList.remove('hidden')
    }

    document.getElementById('deletePopup').addEventListener('click', (e) => {
        if (e.target === document.getElementById('deletePopup')) {
            document.getElementById('deletePopup').classList.add('hidden')
        }
    })

    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', () => {
            const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif']
            const files = Array.from(input.files)
            const invalid = files.filter(f => !allowed.includes(f.type))

            if (invalid.length > 0) {
                alert(`Nelze nahrát: ${invalid.map(f => f.name).join(', ')}\nPovolené formáty: JPG, PNG, WEBP, GIF, AVIF`)
                input.value = ''
            }
        })
    })
</script>
</body>
</html>