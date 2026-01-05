<?php

namespace CRIVenice\Transport\Includes\Elementor\Widgets;

use Elementor\Widget_Base;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Widget Elementor per il form di richiesta di trasporto.
 *
 * @since 1.0.0
 */
class TransportFormWidget extends Widget_Base
{

	public function get_name(): string
	{
		return 'crive-transport-form';
	}

	public function get_title(): string
	{
		return esc_html__('Form Richiesta Trasporto Sanitario', 'cri-trasporti');
	}

	public function get_icon(): string
	{
		return 'eicon-form-horizontal';
	}

	public function get_categories(): array
	{
		return ['cri-venice-category'];
	}

	public function get_script_depends(): array
	{
		return ['crive-form-handler'];
	}

	public function get_style_depends(): array
	{
		return ['crive-form-style'];
	}

	protected function render(): void
	{
		$form_html = '<form id="crive-transport-form" class="crive-transport-form">';

		// Barra di avanzamento
		$form_html .= '<div class="form-progress-bar">';
		$form_html .= '<div class="progress-step active" data-step-indicator="1"><span>1</span><p>' . esc_html__('Trasporto', 'cri-trasporti') . '</p></div>';
		$form_html .= '<div class="progress-step" data-step-indicator="2"><span>2</span><p>' . esc_html__('Luogo', 'cri-trasporti') . '</p></div>';
		$form_html .= '<div class="progress-step" data-step-indicator="3"><span>3</span><p>' . esc_html__('Paziente', 'cri-trasporti') . '</p></div>';
		$form_html .= '</div>';

		$form_html .= '<div class="form-messages"></div>';

		// Pagina 1
		$form_html .= '<div class="form-step active" data-step="1">';
		$form_html .= '<h2>' . esc_html__('Informazioni sul Trasporto', 'cri-trasporti') . '</h2>';
		$form_html .= '<p><label for="nome_cognome">' . esc_html__('Nome e Cognome del Richiedente', 'cri-trasporti') . '</label><input type="text" id="nome_cognome" name="nome_cognome" required></p>';
		$form_html .= '<p><label for="data_trasporto">' . esc_html__('Data del Trasporto', 'cri-trasporti') . '</label><input type="date" id="data_trasporto" name="data_trasporto" required></p>';
		$form_html .= '<p><label for="motivo_trasporto">' . esc_html__('Motivo del Trasporto', 'cri-trasporti') . '</label><select id="motivo_trasporto" name="motivo_trasporto" required>';
		$form_html .= '<option value="">' . esc_html__('Seleziona un motivo...', 'cri-trasporti') . '</option>';
		$form_html .= '<option value="visita">' . esc_html__('Visita', 'cri-trasporti') . '</option>';
		$form_html .= '<option value="trasferimento">' . esc_html__('Trasferimento fra Strutture', 'cri-trasporti') . '</option>';
		$form_html .= '<option value="ricovero">' . esc_html__('Ricovero', 'cri-trasporti') . '</option>';
		$form_html .= '<option value="dimissioni">' . esc_html__('Dimissioni', 'cri-trasporti') . '</option>';
		$form_html .= '<option value="altro">' . esc_html__('Altro', 'cri-trasporti') . '</option>';
		$form_html .= '</select></p>';

		// Campi condizionali per la Pagina 1
		$form_html .= '<div id="visita-fields" class="conditional-field"><p><label for="tipologia_visita">' . esc_html__('Tipologia Visita', 'cri-trasporti') . '</label><input type="text" id="tipologia_visita" name="tipologia_visita"></p><p><label for="orario_visita">' . esc_html__('Orario della Visita', 'cri-trasporti') . '</label><input type="time" id="orario_visita" name="orario_visita"></p><p><label for="tempo_visita">' . esc_html__('Tempo di visita stimato (in minuti)', 'cri-trasporti') . '</label><input type="number" id="tempo_visita" name="tempo_visita"></p></div>';
		$form_html .= '<div id="trasferimento-fields" class="conditional-field"><p><label for="struttura_da">' . esc_html__('Nome Struttura DA', 'cri-trasporti') . '</label><input type="text" id="struttura_da" name="struttura_da" maxlength="100"></p><p><label for="struttura_a">' . esc_html__('Nome Struttura A', 'cri-trasporti') . '</label><input type="text" id="struttura_a" name="struttura_a" maxlength="100"></p></div>';
		$form_html .= '<div id="ricovero-fields" class="conditional-field"><p><label for="orario_ricovero">' . esc_html__('Orario del Ricovero', 'cri-trasporti') . '</label><input type="time" id="orario_ricovero" name="orario_ricovero"></p></div>';
		$form_html .= '<div id="dimissioni-fields" class="conditional-field"><p><label for="orario_dimissioni">' . esc_html__('Orario delle Dimissioni', 'cri-trasporti') . '</label><input type="time" id="orario_dimissioni" name="orario_dimissioni"></p></div>';

		$form_html .= '<div class="form-navigation"><button type="button" class="next-step">' . esc_html__('Avanti', 'cri-trasporti') . '</button></div>';
		$form_html .= '</div>';

		// Pagina 2
		$form_html .= '<div class="form-step" data-step="2">';
		$form_html .= '<h2>' . esc_html__('Luogo di Intervento', 'cri-trasporti') . '</h2>';
		$form_html .= '<p><label for="luogo_intervento">' . esc_html__('Tipo di Luogo', 'cri-trasporti') . '</label><select id="luogo_intervento" name="luogo_intervento" required>';
		$form_html .= '<option value="">' . esc_html__('Seleziona un luogo...', 'cri-trasporti') . '</option>';
		$form_html .= '<option value="domicilio">' . esc_html__('Domicilio', 'cri-trasporti') . '</option>';
		$form_html .= '<option value="rsa">' . esc_html__('RSA', 'cri-trasporti') . '</option>';
		$form_html .= '<option value="ambulatorio">' . esc_html__('Ambulatorio', 'cri-trasporti') . '</option>';
		$form_html .= '<option value="ospedale">' . esc_html__('Ospedale', 'cri-trasporti') . '</option>';
		$form_html .= '</select></p>';
		$form_html .= '<p><label for="indirizzo_intervento">' . esc_html__('Indirizzo del Luogo di Intervento', 'cri-trasporti') . '</label><textarea id="indirizzo_intervento" name="indirizzo_intervento" required></textarea></p>';

		// Campi condizionali per la Pagina 2
		$form_html .= '<div id="struttura-fields" class="conditional-field"><p><label for="nome_struttura">' . esc_html__('Nome della Struttura', 'cri-trasporti') . '</label><input type="text" id="nome_struttura" name="nome_struttura"></p></div>';
		$form_html .= '<div id="domicilio-fields" class="conditional-field">';
		$form_html .= '<p><label for="piano">' . esc_html__('Piano', 'cri-trasporti') . '</label><input type="text" id="piano" name="piano"></p>';
		$form_html .= '<p><label for="ascensore">' . esc_html__('Ascensore', 'cri-trasporti') . '</label><select id="ascensore" name="ascensore">';
		$form_html .= '<option value="">' . esc_html__('Seleziona...', 'cri-trasporti') . '</option>';
		$form_html .= '<option value="presente">' . esc_html__('Presente', 'cri-trasporti') . '</option>';
		$form_html .= '<option value="assente">' . esc_html__('Assente', 'cri-trasporti') . '</option>';
		$form_html .= '</select></p>';
		$form_html .= '<div id="ascensore-details-fields" class="conditional-field"><p><label for="dettagli_ascensore">' . esc_html__('Dettagli Ascensore', 'cri-trasporti') . '</label><select id="dettagli_ascensore" name="dettagli_ascensore"><option value="spazioso">' . esc_html__('Spazioso', 'cri-trasporti') . '</option><option value="stretto">' . esc_html__('Stretto', 'cri-trasporti') . '</option></select></p></div>';
		$form_html .= '<div id="scale-fields" class="conditional-field"><p><label for="dettagli_scale">' . esc_html__('Dettagli Scale', 'cri-trasporti') . '</label><select id="dettagli_scale" name="dettagli_scale"><option value="ampie">' . esc_html__('Ampie', 'cri-trasporti') . '</option><option value="strette">' . esc_html__('Strette', 'cri-trasporti') . '</option></select></p></div>';
		$form_html .= '</div>';

		$form_html .= '<div class="destination-section" style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 20px;">';
		$form_html .= '<h3>' . esc_html__('Luogo di Destinazione', 'cri-trasporti') . '</h3>';
		$form_html .= '<p><label for="indirizzo_destinazione">' . esc_html__('Indirizzo di Destinazione', 'cri-trasporti') . '</label><textarea id="indirizzo_destinazione" name="indirizzo_destinazione" placeholder="' . esc_attr__('Inserisci l\'indirizzo completo di destinazione', 'cri-trasporti') . '" required></textarea></p>';
		$form_html .= '</div>';

		$form_html .= '<p><label for="trasporto_precedente">' . esc_html__('Siamo già venuti a prendere il paziente?', 'cri-trasporti') . '</label><select id="trasporto_precedente" name="trasporto_precedente"><option value="no">' . esc_html__('No', 'cri-trasporti') . '</option><option value="si">' . esc_html__('Sì', 'cri-trasporti') . '</option></select></p>';
		$form_html .= '<div id="attrezzatura-precedente-fields" class="conditional-field"><p><label for="attrezzatura_precedente">' . esc_html__('Quale attrezzatura abbiamo usato?', 'cri-trasporti') . '</label><input type="text" id="attrezzatura_precedente" name="attrezzatura_precedente" placeholder="' . esc_attr__('Es. Sedia portantina, Telo, etc.', 'cri-trasporti') . '"></p></div>';

		$form_html .= '<div class="form-navigation"><button type="button" class="prev-step">' . esc_html__('Indietro', 'cri-trasporti') . '</button><button type="button" class="next-step">' . esc_html__('Avanti', 'cri-trasporti') . '</button></div>';
		$form_html .= '</div>';

		// Pagina 3
		$form_html .= '<div class="form-step" data-step="3">';
		$form_html .= '<h2>' . esc_html__('Dati Anagrafici del Paziente', 'cri-trasporti') . '</h2>';
		$form_html .= '<p><label for="data_nascita">' . esc_html__('Data di Nascita', 'cri-trasporti') . '</label><input type="date" id="data_nascita" name="data_nascita"></p>';
		$form_html .= '<p><label for="luogo_nascita">' . esc_html__('Luogo di Nascita', 'cri-trasporti') . '</label><input type="text" id="luogo_nascita" name="luogo_nascita"></p>';
		$form_html .= '<p><label for="codice_fiscale">' . esc_html__('Codice Fiscale', 'cri-trasporti') . '</label><input type="text" id="codice_fiscale" name="codice_fiscale"></p>';
		$form_html .= '<p><label for="recapito_telefonico">' . esc_html__('Recapito Telefonico (Paziente o Contatto di Riferimento)', 'cri-trasporti') . '</label><input type="tel" id="recapito_telefonico" name="recapito_telefonico" required></p>';
		$form_html .= '<p><label for="recapito_email">' . esc_html__('Recapito Email (Paziente o Contatto di Riferimento)', 'cri-trasporti') . '</label><input type="email" id="recapito_email" name="recapito_email" required></p>';
		$form_html .= '<div class="form-navigation"><button type="button" class="prev-step">' . esc_html__('Indietro', 'cri-trasporti') . '</button><button type="submit">' . esc_html__('Invia Richiesta', 'cri-trasporti') . '</button></div>';
		$form_html .= '</div>';

		$form_html .= '</form>';
		echo $form_html;
	}

	protected function content_template(): void
	{
		echo '<div class="elementor-alert elementor-alert-info" role="alert">';
		echo 'Form di Richiesta Trasporto CRI. Il form è visibile solo nell\'anteprima della pagina.';
		echo '</div>';
	}
}
