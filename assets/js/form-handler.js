document.addEventListener('DOMContentLoaded', function () {
	const form = document.getElementById('crive-transport-form');
	if (!form) return;

	const steps = form.querySelectorAll('.form-step');
	const progressSteps = form.querySelectorAll('.progress-step');
	const formMessages = form.querySelector('.form-messages');
	let currentStep = 1;

	function updateProgressBar(stepNumber) {
		progressSteps.forEach((step, index) => {
			const indicator = step.dataset.stepIndicator;
			if (indicator < stepNumber) {
				step.classList.add('completed');
				step.classList.remove('active');
			} else if (indicator == stepNumber) {
				step.classList.add('active');
				step.classList.remove('completed');
			} else {
				step.classList.remove('active', 'completed');
			}
		});
	}

	function showStep(stepNumber) {
		steps.forEach(step => step.classList.remove('active'));
		const nextStep = form.querySelector(`.form-step[data-step="${stepNumber}"]`);
		if (nextStep) {
			nextStep.classList.add('active');
			updateProgressBar(stepNumber);
		}
	}

	form.addEventListener('click', function (e) {
		if (e.target.matches('.next-step')) {
			currentStep++;
			showStep(currentStep);
		} else if (e.target.matches('.prev-step')) {
			currentStep--;
			showStep(currentStep);
		}
	});

	// --- Gestione Campi Condizionali ---
	function handleConditionalFields() {
		form.querySelectorAll('.conditional-field').forEach(field => {
			field.style.display = 'none';
		});

		const motivoSelect = form.querySelector('select[name="motivo_trasporto"]');
		if (motivoSelect.value) {
			const fieldToShow = form.querySelector(`#${motivoSelect.value}-fields`);
			if (fieldToShow) {
				fieldToShow.style.display = 'block';
			}
		}

		const luogoSelect = form.querySelector('select[name="luogo_intervento"]');
		if (luogoSelect.value) {
			if (['rsa', 'ambulatorio', 'ospedale'].includes(luogoSelect.value)) {
				form.querySelector('#struttura-fields').style.display = 'block';
			} else if (luogoSelect.value === 'domicilio') {
				form.querySelector('#domicilio-fields').style.display = 'block';
			}
		}

		const ascensoreSelect = form.querySelector('select[name="ascensore"]');
		if (ascensoreSelect && ascensoreSelect.value === 'no' && form.querySelector('select[name="luogo_intervento"]').value === 'domicilio') {
			form.querySelector('#scale-fields').style.display = 'block';
		}
	}

	form.querySelectorAll('select').forEach(select => {
		select.addEventListener('change', handleConditionalFields);
	});

	handleConditionalFields();


	// --- Gestione Invio Form (AJAX) ---
	form.addEventListener('submit', function (e) {
		e.preventDefault();

		const submitButton = form.querySelector('button[type="submit"]');
		const formData = new FormData(form);
		formData.append('action', 'cri_submit_transport_request');
		formData.append('security', crive_form_data.nonce);

		submitButton.disabled = true;
		submitButton.textContent = 'Invio in corso...';
		formMessages.innerHTML = '';

		fetch(crive_form_data.ajax_url, {
			method: 'POST',
			body: formData,
		})
			.then(response => response.json())
			.then(result => {
				if (result.success) {
					form.innerHTML = `<div class="form-success">${result.data.message}</div>`;
				} else {
					formMessages.innerHTML = `<div class="form-error">${result.data.message}</div>`;
					submitButton.disabled = false;
					submitButton.textContent = 'Invia Richiesta';
				}
			})
			.catch(() => {
				formMessages.innerHTML = '<div class="form-error">Si è verificato un errore di rete. Riprova.</div>';
				submitButton.disabled = false;
				submitButton.textContent = 'Invia Richiesta';
			});
	});
});
