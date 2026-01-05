document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('crive-details-modal');
    const closeBtn = document.querySelector('.crive-modal-close');
    const detailsContainer = document.getElementById('crive-modal-content-body');

    if (!modal) return;

    // Modifica logica apertura/chiusura per usare classi Tailwind
    function openModal() {
        modal.classList.remove('tw-hidden');
        modal.classList.add('tw-flex');
    }

    function closeModal() {
        modal.classList.add('tw-hidden');
        modal.classList.remove('tw-flex');
    }

    // Delegate click event for the dynamically generated buttons
    document.querySelector('.wp-list-table').addEventListener('click', function (e) {
        // Cerca il pulsante o un suo genitore
        const btn = e.target.closest('.view-details-btn');
        if (btn) {
            e.preventDefault();
            const rawData = btn.getAttribute('data-details');

            try {
                const data = JSON.parse(rawData);
                populateModal(data);
                openModal();
            } catch (error) {
                console.error('Errore nel parsing dei dati JSON', error);
                alert('Impossibile caricare i dettagli.');
            }
        }
    });

    closeBtn.addEventListener('click', closeModal);

    window.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    function populateModal(data) {
        let html = '<div class="tw-overflow-hidden tw-border tw-border-gray-200 tw-rounded-lg"><table class="tw-min-w-full tw-divide-y tw-divide-gray-200">';

        let isAlt = false;
        for (const [key, value] of Object.entries(data)) {
            // Salta campi vuoti o tecnici se necessario
            if (value === '' || value === null) continue;

            let label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

            // Gestione etichette specifiche
            const labelMap = {
                'nome_cognome': 'Nome e Cognome',
                'data_trasporto': 'Data Trasporto',
                'recapito_telefonico': 'Telefono',
                'recapito_email': 'Email',
                'motivo_trasporto': 'Motivo',
                'luogo_intervento': 'Luogo Ritiro',
                'indirizzo_intervento': 'Indirizzo Ritiro',
                'indirizzo_destinazione': 'Indirizzo Destinazione',
                'struttura_da': 'Da Struttura',
                'struttura_a': 'A Struttura',
                'piano': 'Piano',
                'ascensore': 'Ascensore',
                'dettagli_scale': 'Dettagli Scale',
                'codice_fiscale': 'Codice Fiscale'
            };

            if (labelMap[key]) {
                label = labelMap[key];
            }

            const bgClass = isAlt ? 'tw-bg-gray-50' : 'tw-bg-white';
            html += `<tr class="${bgClass}">
                <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider tw-w-1/3">${label}</th>
                <td class="tw-px-6 tw-py-3 tw-text-sm tw-text-gray-900">${formatValue(key, value)}</td>
            </tr>`;
            isAlt = !isAlt;
        }

        html += '</table></div>';
        detailsContainer.innerHTML = html;
    }

    function formatValue(key, value) {
        if (key === 'ascensore') {
            if (value === '1' || value === 1 || value === true || value === 'presente') return 'Presente';
            if (value === '0' || value === 0 || value === false || value === 'assente') return 'Assente';
            return value;
        }
        return value;
    }
});
