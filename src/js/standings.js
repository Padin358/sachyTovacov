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

fetch('../data_mladez.json')
    .then(response => response.json())
    .then(data => {
        const info = document.querySelector('#youth-standings-info');
        const tbody = document.querySelector('#youth-standings-tbody');
        const textList = document.querySelector('#youth-text-list');
        const scheduleWrap = document.querySelector('#youth-schedules');

        if (info) {
            info.textContent = `${data.kolo}. losovací kolo od ${data.datum}`;
        }

        if (tbody) {
            tbody.innerHTML = '';

            (data.skupiny || []).forEach((skupina, index) => {
                const tr = document.createElement('tr');
                tr.className = index % 2 === 0 ? 'bg-[#111]' : 'bg-[#0d0d0d]';

                const players = [0, 1, 2, 3].map((playerIndex) => {
                    const hrac = skupina.hraci?.[playerIndex];
                    return hrac ? `${hrac.cislo}. ${hrac.jmeno}` : '-';
                });

                tr.innerHTML = `
                    <td class="border border-[#333] p-2 font-semibold">${skupina.nazev}</td>
                    <td class="border border-[#333] p-2">${players[0]}</td>
                    <td class="border border-[#333] p-2">${players[1]}</td>
                    <td class="border border-[#333] p-2">${players[2]}</td>
                    <td class="border border-[#333] p-2">${players[3]}</td>
                `;

                tbody.appendChild(tr);
            });
        }

        if (textList) {
            textList.innerHTML = '';
            (data.doprovodny_text || []).forEach((text) => {
                const li = document.createElement('li');
                li.className = 'text-gray-300';
                li.textContent = text;
                textList.appendChild(li);
            });
        }

        if (scheduleWrap) {
            scheduleWrap.innerHTML = '';

            (data.rozpisy || []).forEach((rozpis) => {
                const section = document.createElement('section');
                section.className = 'mt-8';
                section.innerHTML = `
                    <h3 class="text-xl font-semibold mb-3 text-[#efefef]">${rozpis.nazev}</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-[#dfdfdf]">
                            <tbody>
                                ${(rozpis.radky || []).map((radek, index) => `
                                    <tr class="${index % 2 === 0 ? 'bg-[#111]' : 'bg-[#0d0d0d]'}">
                                        <td class="border border-[#333] p-2 font-semibold">${radek.kolo}</td>
                                        <td class="border border-[#333] p-2 text-center">${radek.zapasy?.[0] || '-'}</td>
                                        <td class="border border-[#333] p-2 text-center">${radek.zapasy?.[1] || '-'}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;

                scheduleWrap.appendChild(section);
            });
        }
    })
    .catch(error => console.error('Chyba pri nacitani dat:', error));
