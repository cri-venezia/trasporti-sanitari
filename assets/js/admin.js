document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('crive-details-modal');
    const closeBtn = document.querySelector('.crive-modal-close');
    const detailsContainer = document.getElementById('crive-modal-content-body');

    if (!modal) return;

    // Delegate click event for the dynamically generated buttons
    document.querySelector('.wp-list-table').addEventListener('click', function(e) {
        if (e.target.classList.contains('view-details-btn')) {
            e.preventDefault();
            const rawData = e.target.getAttribute('data-details');
            
            try {
                const data = JSON.parse(rawData);
                populateModal(data);
                modal.classList.add('open');
            } catch (error) {
                console.error('Errore nel parsing dei dati JSON', error);
                alert('Impossibile caricare i dettagli.');
            }
        }
    });

    closeBtn.addEventListener('click', function() {
        modal.classList.remove('open');
    });

    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('open');
        }
    });

    function populateModal(data) {
        let html = '<table class="crive-details-table">';
        
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
                'struttura_a': 'A Struttura'
            };

            if (labelMap[key]) {
                label = labelMap[key];
            }

            html += `<tr><th>${label}</th><td>${formatValue(key, value)}</td></tr>`;
        }
        
        html += '</table>';
        detailsContainer.innerHTML = html;
    }

    function formatValue(key, value) {
        if (key === 'ascensore') {
            // Il DB salva 1/0 o 'presente'/'assente'? RequestManager mappa a 1/0, ma controlliamo
            if (value === '1' || value === 1) return 'Presente';
            if (value === '0' || value === 0) return 'Assente';
            return value; // Fallback
        }
        return value;
    }
});
