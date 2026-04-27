const categoryClasses = {
    'Turnaj': 'news-tag--turnaj',
    'Úspěch': 'news-tag--uspech',
    'Oznámení': 'news-tag--oznameni',
    'Výsledky': 'news-tag--vysledky',
    'Jiné': 'news-tag--jine',
};

const PEREX_LENGTH = 150;

fetch('../data_novinky.json?v=' + Date.now())
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('news-container');
        container.innerHTML = '';

        if (!Array.isArray(data) || data.length === 0) {
            container.innerHTML = '<article class="news-empty">Zatím tu nejsou žádné novinky.</article>';
            return;
        }

        data.slice().reverse().forEach(clanek => {
            const tagClass = categoryClasses[clanek.kategorie] ?? categoryClasses['Jiné'];
            const perex = clanek.text.length > PEREX_LENGTH
                ? clanek.text.substring(0, PEREX_LENGTH) + '...'
                : clanek.text;

            const article = document.createElement('article');
            article.className = 'news-card';

            if (clanek.obrazek) {
                article.innerHTML = `
                    <div class="news-card__layout">
                        <div class="news-media">
                            <img src="../${clanek.obrazek}" alt="${clanek.nadpis}" class="w-full h-full object-cover rounded-md">
                        </div>
                        <div class="news-body">
                            ${articleContent(clanek, tagClass, perex)}
                        </div>
                    </div>`;
            } else {
                article.innerHTML = `
                    <div class="news-body">
                        ${articleContent(clanek, tagClass, perex)}
                    </div>
                `;
            }

            container.appendChild(article);
        });
    })
    .catch(err => console.error('Chyba při načítání novinek:', err));

function articleContent(clanek, tagClass, perex) {
    return `
        <div class="news-meta">
            <span class="news-date">${clanek.datum}</span>
            <span class="news-tag ${tagClass}">${clanek.kategorie}</span>
        </div>
        <h3 class="news-title">${clanek.nadpis}</h3>
        <p class="news-text">${perex}</p>
        <a href="clanek.php?id=${clanek.id}" class="news-readmore">Číst více</a>
    `;
}
