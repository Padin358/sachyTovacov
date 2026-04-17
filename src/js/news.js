const categoryColors = {
    'Turnaj':   { bg: 'bg-blue-600/20',   text: 'text-blue-400' },
    'Úspěch':   { bg: 'bg-green-600/20',  text: 'text-green-400' },
    'Oznámení': { bg: 'bg-yellow-600/20', text: 'text-yellow-400' },
    'Výsledky': { bg: 'bg-purple-600/20', text: 'text-purple-400' },
    'Jiné':     { bg: 'bg-gray-600/20',   text: 'text-gray-400' },
}

const PEREX_LENGTH = 150

fetch('../data_novinky.json?v=' + Date.now())
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('news-container')
        container.innerHTML = ''

        data.slice().reverse().forEach(clanek => {
            const barva = categoryColors[clanek.kategorie] ?? categoryColors['Jiné']
            const perex = clanek.text.length > PEREX_LENGTH
                ? clanek.text.substring(0, PEREX_LENGTH) + '...'
                : clanek.text

            const article = document.createElement('article')
            article.className = 'bg-[#111] rounded-lg overflow-hidden hover:bg-[#1a1a1a] transition-colors'

            if (clanek.obrazek) {
                article.innerHTML = `
                    <div class="grid md:grid-cols-3">
                        <div class="md:col-span-1 bg-[#222] flex items-center justify-center p-8">
                            <img src="../${clanek.obrazek}" alt="${clanek.nadpis}" class="w-full h-full object-cover rounded-md">
                        </div>
                        <div class="md:col-span-2 p-6">
                            ${articleContent(clanek, barva, perex)}
                        </div>
                    </div>`
            } else {
                article.innerHTML = `<div class="p-6">${articleContent(clanek, barva, perex)}</div>`
            }

            container.appendChild(article)
        })
    })
    .catch(err => console.error('Chyba při načítání novinek:', err))

function articleContent(clanek, barva, perex) {
    return `
        <div class="flex items-center gap-3 mb-3">
            <span class="text-sm text-gray-400">${clanek.datum}</span>
            <span class="px-3 py-1 ${barva.bg} ${barva.text} text-xs rounded-full">${clanek.kategorie}</span>
        </div>
        <h3 class="text-2xl font-bold mb-3 text-[#efefef]">${clanek.nadpis}</h3>
        <p class="text-gray-300 mb-4 leading-relaxed">${perex}</p>
        <a href="clanek.php?id=${clanek.id}" class="text-blue-400 hover:text-blue-300 transition-colors text-sm font-semibold">Číst více →</a>
    `
}
