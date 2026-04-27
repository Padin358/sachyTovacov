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
                tr.className = 'table-row';
                tr.innerHTML = renderRow(item);
                tbody.appendChild(tr);
            });
        })
        .catch(error => console.error('Chyba pri nacitani dat:', error));
}

renderTableRows('../data_tabulka.json', '#standings-tbody', (clen) => `
    <td class="table-cell" data-label="Jméno hráče">${clen.jmeno}</td>
    <td class="table-cell table-cell--center" data-label="Ročník">${clen.rocnik || '-'}</td>
    <td class="table-cell table-cell--center" data-label="ELO">${clen.elo || '-'}</td>
    <td class="table-cell table-cell--center" data-label="ELO-R">${clen.elo_r || '-'}</td>
    <td class="table-cell table-cell--center" data-label="Registrace">${clen.registrace || '-'}</td>
`);
