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

$categoryClasses = [
    'Turnaj' => 'news-tag--turnaj',
    'Úspěch' => 'news-tag--uspech',
    'Oznámení' => 'news-tag--oznameni',
    'Výsledky' => 'news-tag--vysledky',
    'Jiné' => 'news-tag--jine',
];

$tagClass = $categoryClasses[$clanek['kategorie']] ?? $categoryClasses['Jiné'];
$galerie = $clanek['galerie'] ?? [];
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../output.css">
    <link rel="stylesheet" href="../theme.css">
    <title>Šachy Tovačov &raquo; <?php echo htmlspecialchars($clanek['nadpis']); ?></title>
</head>
<body class="site-body">
    <header class="site-header">
        <div class="topbar">
            <div class="topbar-inner">
                <a href="../" class="brand-mark">
                    <span class="brand-mark__piece" aria-hidden="true">&#9812;</span>
                    <span>Šachy Tovačov</span>
                </a>
                <nav class="site-nav" aria-label="Hlavní navigace">
                    <a href="../" class="nav-link">Úvod</a>
                    <a href="./" class="nav-link is-active">Novinky</a>
                    <a href="../players/" class="nav-link">Hráči</a>
                    <a href="../standings/" class="nav-link">Tabulky</a>
                    <a href="../contact/" class="nav-link">Kontakt</a>
                </nav>
            </div>
        </div>
        <section class="hero">
            <div class="hero-inner">
                <div class="hero-copy">
                    <p class="hero-kicker">Detail novinky</p>
                    <p class="hero-title">Klubové dění</p>
                    <p class="hero-text"><?php echo htmlspecialchars($clanek['nadpis']); ?></p>
                    <div class="hero-badges">
                        <span class="hero-badge"><?php echo htmlspecialchars($clanek['datum']); ?></span>
                        <span class="hero-badge"><?php echo htmlspecialchars($clanek['kategorie']); ?></span>
                    </div>
                </div>
                <div class="hero-piece" aria-hidden="true">&#9813;</div>
            </div>
        </section>
    </header>

    <main class="article-shell">
        <a href="../news/" class="article-back">&#8592; Zpět na novinky</a>

        <article class="article-card">
            <?php if (!empty($clanek['obrazek'])): ?>
                <img src="../<?php echo htmlspecialchars($clanek['obrazek']); ?>" alt="<?php echo htmlspecialchars($clanek['nadpis']); ?>" class="article-image">
            <?php endif; ?>

            <div class="article-body">
                <div class="news-meta">
                    <span class="news-date"><?php echo htmlspecialchars($clanek['datum']); ?></span>
                    <span class="news-tag <?php echo $tagClass; ?>"><?php echo htmlspecialchars($clanek['kategorie']); ?></span>
                </div>

                <h1 class="article-title"><?php echo htmlspecialchars($clanek['nadpis']); ?></h1>

                <div class="article-text"><?php echo nl2br(htmlspecialchars($clanek['text'])); ?></div>

                <?php if (!empty($galerie)): ?>
                    <section class="article-gallery">
                        <h2 class="article-gallery__title">Fotogalerie</h2>
                        <div class="article-gallery__grid">
                            <?php foreach ($galerie as $img): ?>
                                <button type="button" onclick="openLightbox('<?php echo htmlspecialchars('../' . $img); ?>')" class="article-gallery__button">
                                    <img src="../<?php echo htmlspecialchars($img); ?>" alt="Fotografie k článku">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </article>
    </main>

    <div id="lightbox" class="lightbox" onclick="closeLightbox()">
        <button type="button" onclick="closeLightbox()" class="lightbox__close" aria-label="Zavřít náhled">&times;</button>
        <img id="lightbox-img" src="" alt="" class="lightbox__image">
    </div>

    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-grid">
                <div>
                    <h3 class="footer-title">Šachy Tovačov</h3>
                    <p class="footer-text">Šachový klub pro děti, mládež i dospělé hráče se zaměřením na trénink a účast v soutěžích.</p>
                </div>
                <div>
                    <h3 class="footer-title">Rychlé odkazy</h3>
                    <div class="footer-links">
                        <a href="../news/">Novinky</a>
                        <a href="../players/">Hráči</a>
                        <a href="../standings/">Tabulky</a>
                        <a href="../contact/">Kontakt</a>
                    </div>
                </div>
                <div>
                    <h3 class="footer-title">Kontakt</h3>
                    <p class="footer-text">Email: <a href="mailto:calajiri@seznam.cz">calajiri@seznam.cz</a></p>
                    <p class="footer-text">Telefon: <a href="tel:+420608618163">+420 608 618 163</a></p>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="footer-bottom-flex">
                    <p>Website created by RV&MZ with 💙</p>
                    <p>&copy; 2026 Šachy Tovačov. Všechna práva vyhrazena.</p>
                    <a href="mailto:calajiri@seznam.cz">Napište nám</a>
                </div>
                <div class="admin-link-div">
                    <a href="../login/" class="admin-link">Admin Panel</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function openLightbox(src) {
            document.getElementById('lightbox-img').src = src;
            document.getElementById('lightbox').classList.add('is-open');
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('is-open');
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeLightbox();
            }
        });
    </script>
</body>
</html>
