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
                tr.className = 'table-row';

                const players = [0, 1, 2, 3].map((playerIndex) => {
                    const hrac = skupina.hraci?.[playerIndex];
                    return hrac ? `${hrac.cislo}. ${hrac.jmeno}` : '-';
                });

                tr.innerHTML = `
                    <td class="table-cell" data-label="Skupina">${skupina.nazev}</td>
                    <td class="table-cell" data-label="Pořadí 1">${players[0]}</td>
                    <td class="table-cell" data-label="Pořadí 2">${players[1]}</td>
                    <td class="table-cell" data-label="Pořadí 3">${players[2]}</td>
                    <td class="table-cell" data-label="Pořadí 4">${players[3]}</td>
                `;

                tbody.appendChild(tr);
            });
        }

        if (textList) {
            textList.innerHTML = '';
            (data.doprovodny_text || []).forEach((text) => {
                const li = document.createElement('li');
                li.className = 'note-item';
                li.textContent = text;
                textList.appendChild(li);
            });
        }

        if (scheduleWrap) {
            scheduleWrap.innerHTML = '';

            (data.rozpisy || []).forEach((rozpis) => {
                const section = document.createElement('section');
                section.className = 'schedule-card';
                section.innerHTML = `
                    <h3 class="card-title">${rozpis.nazev}</h3>
                    <div class="table-scroll">
                        <table class="standings-table">
                            <tbody>
                                ${(rozpis.radky || []).map((radek) => `
                                    <tr class="table-row">
                                        <td class="table-cell" data-label="Kolo">${radek.kolo}</td>
                                        <td class="table-cell table-cell--center" data-label="Zápas 1">${radek.zapasy?.[0] || '-'}</td>
                                        <td class="table-cell table-cell--center" data-label="Zápas 2">${radek.zapasy?.[1] || '-'}</td>
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
