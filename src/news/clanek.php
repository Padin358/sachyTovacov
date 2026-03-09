<?php
$file = '../data_novinky.json';
$data = json_decode(file_get_contents($file), true);
$id = (int)($_GET['id'] ?? 0);

$clanek = null;
foreach ($data as $item) {
    if ($item['id'] === $id) {
        $clanek = $item;
        break;
    }
}

if (!$clanek) {
    header('Location: ../news/');
    exit;
}

$categoryColors = [
    'Turnaj'   => 'bg-blue-600/20 text-blue-400',
'Úspěch'   => 'bg-green-600/20 text-green-400',
'Oznámení' => 'bg-yellow-600/20 text-yellow-400',
'Výsledky' => 'bg-purple-600/20 text-purple-400',
'Jiné'     => 'bg-gray-600/20 text-gray-400',
];
$barva = $categoryColors[$clanek['kategorie']] ?? $categoryColors['Jiné'];
$galerie = $clanek['galerie'] ?? [];
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../output.css">
    <title>Šachy Tovačov &raquo; <?php echo htmlspecialchars($clanek['nadpis']); ?></title>
</head>
<body class="bg-[#0a0a0a] text-[#dfdfdf] min-h-screen flex flex-col">
<header>
    <div class="p-4 bg-[#111] flex flex-row flex-wrap justify-center md:justify-end gap-5 text-[#dfdfdf] font-semibold text-lg">
        <a href="../" class="after:block after:border-b-2 after:scale-x-0 after:origin-bottom after:transition-all after:duration-200 hover:after:scale-x-100">Úvod</a>
        <a href="../news/" class="after:block after:border-b-2 after:scale-x-100 after:origin-bottom after:transition-all after:duration-200 hover:after:scale-x-100">Novinky</a>
        <a href="../standings/" class="after:block after:border-b-2 after:scale-x-0 after:origin-bottom after:transition-all after:duration-200 hover:after:scale-x-100">Tabulky</a>
        <a href="../contact/" class="after:block after:border-b-2 after:scale-x-0 after:origin-bottom after:transition-all after:duration-200 hover:after:scale-x-100">Kontakt</a>
    </div>
</header>

<main class="max-w-3xl mx-auto px-4 py-8 flex-grow w-full">
    <a href="../news/" class="text-blue-400 hover:text-blue-300 transition-colors text-sm font-semibold mb-6 inline-block">← Zpět na novinky</a>

    <?php if (!empty($clanek['obrazek'])): ?>
    <div class="rounded-lg overflow-hidden mb-6">
        <img src="../<?php echo htmlspecialchars($clanek['obrazek']); ?>" alt="<?php echo htmlspecialchars($clanek['nadpis']); ?>" class="w-full object-cover max-h-96">
    </div>
    <?php endif; ?>

    <div class="flex items-center gap-3 mb-3">
        <span class="text-sm text-gray-400"><?php echo htmlspecialchars($clanek['datum']); ?></span>
        <span class="px-3 py-1 <?php echo $barva; ?> text-xs rounded-full"><?php echo htmlspecialchars($clanek['kategorie']); ?></span>
    </div>

    <h1 class="text-3xl font-bold mb-6 text-[#efefef]"><?php echo htmlspecialchars($clanek['nadpis']); ?></h1>
    <div class="text-gray-300 leading-relaxed mb-8 break-words"><?php echo nl2br(htmlspecialchars($clanek['text'])); ?></div>

    <?php if (!empty($galerie)): ?>
    <h2 class="text-xl font-semibold mb-4 text-[#efefef]">Fotogalerie</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <?php foreach ($galerie as $img): ?>
        <button onclick="openLightbox('<?php echo htmlspecialchars('../' . $img); ?>')" class="overflow-hidden rounded-lg cursor-pointer">
            <img src="../<?php echo htmlspecialchars($img); ?>" class="w-full h-36 object-cover hover:scale-105 transition-transform duration-300">
        </button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<!-- Lightbox -->
<div id="lightbox" class="fixed inset-0 bg-black/90 hidden items-center justify-center z-50" onclick="closeLightbox()">
    <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white text-3xl leading-none">&times;</button>
    <img id="lightbox-img" src="" class="max-w-[90vw] max-h-[90vh] object-contain rounded-lg">
</div>

<footer class="bg-[#111] mt-auto py-8 border-t border-[#333]">
    <div class="max-w-6xl mx-auto px-4 text-center text-gray-500 text-sm">
        <p>&copy; 2026 Šachy Tovačov. Všechna práva vyhrazena.</p>
    </div>
</footer>

<script>
    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src
        const lb = document.getElementById('lightbox')
        lb.classList.remove('hidden')
        lb.classList.add('flex')
    }

    function closeLightbox() {
        const lb = document.getElementById('lightbox')
        lb.classList.add('hidden')
        lb.classList.remove('flex')
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeLightbox()
    })
</script>
</body>
</html>