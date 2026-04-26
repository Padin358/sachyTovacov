function renderTableRows(url, tbodySelector, renderRow) {
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector(tbodySelector);

            if (!tbody) {
                return;
            }

            tbody.innerHTML = '';

            data.forEach((item, index) => {
                const tr = document.createElement('tr');
                tr.className = index % 2 === 0 ? 'bg-[#111]' : 'bg-[#0d0d0d]';
                tr.innerHTML = renderRow(item);
                tbody.appendChild(tr);
            });
        })
        .catch(error => console.error('Chyba pri nacitani dat:', error));
}

renderTableRows('../data_tabulka.json', '#standings-tbody', (clen) => `
    <td class="border border-[#333] p-2">${clen.jmeno}</td>
    <td class="border border-[#333] p-2 text-center">${clen.rocnik || '-'}</td>
    <td class="border border-[#333] p-2 text-center">${clen.elo || '-'}</td>
    <td class="border border-[#333] p-2 text-center">${clen.elo_r || '-'}</td>
    <td class="border border-[#333] p-2 text-center">${clen.registrace || '-'}</td>
`);
