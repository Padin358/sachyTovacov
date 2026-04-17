// Nacteni dat z JSON souboru
fetch('../data_tabulka.json')
    .then(response => response.json())
    .then(data => {
        const tbody = document.querySelector('#standings-tbody');

        tbody.innerHTML = '';

        data.forEach((clen, index) => {
            const tr = document.createElement('tr');
            tr.className = index % 2 === 0 ? 'bg-[#111]' : 'bg-[#0d0d0d]';

            tr.innerHTML = `
                <td class="border border-[#333] p-2">${clen.jmeno}</td>
                <td class="border border-[#333] p-2 text-center">${clen.rocnik || '-'}</td>
                <td class="border border-[#333] p-2 text-center">${clen.elo || '-'}</td>
                <td class="border border-[#333] p-2 text-center">${clen.elo_r || '-'}</td>
                <td class="border border-[#333] p-2 text-center">${clen.registrace || '-'}</td>
            `;

            tbody.appendChild(tr);
        });
    })
    .catch(error => console.error('Chyba pri nacitani dat:', error));
