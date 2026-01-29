// Načtení dat z JSON souboru
fetch('../data_tabulka.json')
    .then(response => response.json())
    .then(data => {
        const tbody = document.querySelector('#standings-tbody');
        
        // Vymazat existující řádky (pokud jsou nějaké)
        tbody.innerHTML = '';
        
        // Projít data a vytvořit řádky
        data.forEach((clen, index) => {
            const tr = document.createElement('tr');
            tr.className = index % 2 === 0 ? 'bg-[#111]' : 'bg-[#0d0d0d]';
            
            tr.innerHTML = `
                <td class="border border-[#333] p-2">${clen.jmeno}</td>
                <td class="border border-[#333] p-2">${clen.datum_narozeni}</td>
                <td class="border border-[#333] p-2">${clen.vt || '-'}</td>
                <td class="border border-[#333] p-2">${clen.c_lok}</td>
                <td class="border border-[#333] p-2">${clen.elo_cr}</td>
                <td class="border border-[#333] p-2">${clen.elo_rapid}</td>
                <td class="border border-[#333] p-2">${clen.elo_fide || '-'}</td>
            `;
            
            tbody.appendChild(tr);
        });
    })
    .catch(error => console.error('Chyba při načítání dat:', error));