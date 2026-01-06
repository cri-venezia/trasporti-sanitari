<?php

namespace CRIVenice\Transport\Includes;

use CRIVenice\Transport\Enums\RequestStatus;
use WP_Error;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Gestisce la logica di business per la creazione e la gestione delle richieste.
 *
 * @since 1.0.0
 */
class RequestManager
{

	/**
	 * Elabora e salva una nuova richiesta di trasporto.
	 *
	 * @param array $post_data Dati grezzi dal form ($_POST).
	 * @return int|WP_Error L'ID della richiesta in caso di successo, altrimenti un oggetto WP_Error.
	 */
	public function process_new_request(array $post_data): int|WP_Error
	{
		$sanitized_data = $this->sanitize_and_validate_request_data($post_data);

		if (is_wp_error($sanitized_data)) {
			return $sanitized_data;
		}

		$insert_id = $this->insert_request_into_db($sanitized_data);
		if (! $insert_id) {
			return new WP_Error('db_error', esc_html__('Si è verificato un errore durante il salvataggio della richiesta.', 'cri-trasporti'));
		}

		// Prova a inviare le notifiche e controlla il risultato
		$notifications_sent = $this->send_notifications($sanitized_data, $insert_id);
		if (! $notifications_sent) {
			// Opzionale: Logga l'errore se l'invio fallisce, ma non bloccare il processo principale.
			error_log('CRIVE Trasporti: Invio notifiche fallito per richiesta ID ' . $insert_id);
		}


		return $insert_id;
	}

	/**
	 * Aggiorna una richiesta di trasporto esistente.
	 *
	 * @param int $request_id ID della richiesta da aggiornare.
	 * @param array $post_data Dati grezzi dal form ($_POST).
	 * @return bool|WP_Error True in caso di successo, altrimenti un oggetto WP_Error.
	 */
	public function update_request(int $request_id, array $post_data): bool|WP_Error
	{
		$sanitized_data = $this->sanitize_and_validate_request_data($post_data);

		if (is_wp_error($sanitized_data)) {
			return $sanitized_data;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'crive_transport_requests';

		$db_data = [
			'nome_cognome'        => $sanitized_data['nome_cognome'],
			'data_trasporto'      => $sanitized_data['data_trasporto'],
			'recapito_telefonico' => $sanitized_data['recapito_telefonico'],
			'recapito_email'      => $sanitized_data['recapito_email'],
			'motivo_trasporto'    => $sanitized_data['motivo_trasporto'],
			'luogo_intervento'    => $sanitized_data['luogo_intervento'],
			'indirizzo_intervento' => $sanitized_data['indirizzo_intervento'],
			'piano'               => $sanitized_data['piano'],
			'ascensore'           => ($sanitized_data['ascensore'] === 'presente' ? 1 : 0),
			'larghezza_scale'     => $sanitized_data['dettagli_scale'],
			'codice_fiscale'      => $sanitized_data['codice_fiscale'],
			'dettagli_trasporto'  => wp_json_encode($sanitized_data),
			// Nota: Non aggiorniamo 'created_at' o 'status' qui, a meno che non sia richiesto esplicitamente
		];

	public function update_request(int $request_id, array $post_data): bool|WP_Error
	{
		// ... existing code ...
		return true;
	}

	/**
	 * Aggiorna solo lo stato di una richiesta (per uso interno admin).
	 *
	 * @param int $request_id
	 * @param RequestStatus $status
	 * @return bool
	 */
	public function update_status(int $request_id, RequestStatus $status): bool
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'crive_transport_requests';
		$updated = $wpdb->update(
			$table_name,
			['status' => $status->value],
			['id' => $request_id]
		);
		return $updated !== false;
	}

	/**
	 * Sanifica e valida i dati di una richiesta.
	 *
	 * @param array $post_data Dati grezzi.
	 * @return array|WP_Error Dati sanificati o un errore.
	 */
	private function sanitize_and_validate_request_data(array $post_data): array|WP_Error
	{
		$data = [];
		$errors = new WP_Error();

		$required_fields = [
			'nome_cognome'        => esc_html__('Nome e Cognome Richiedente', 'cri-trasporti'),
			'data_trasporto'      => esc_html__('Data del Trasporto', 'cri-trasporti'),
			'recapito_telefonico' => esc_html__('Recapito Telefonico', 'cri-trasporti'),
			'recapito_email'      => esc_html__('Recapito Email', 'cri-trasporti'),
		];

		foreach ($required_fields as $field => $label) {
			if (empty($post_data[$field])) {
				$errors->add('field_required', sprintf(esc_html__('Il campo "%s" è obbligatorio.', 'cri-trasporti'), $label));
			}
		}

		if (! empty($post_data['recapito_email']) && ! is_email($post_data['recapito_email'])) {
			$errors->add('invalid_email', esc_html__('L\'indirizzo email inserito non è valido.', 'cri-trasporti'));
		}

		if ($errors->has_errors()) {
			return $errors;
		}

		// Sanificazione di tutti i campi
		$fields_to_sanitize = [
			'nome_cognome',
			'motivo_trasporto',
			'tipologia_visita',
			'orario_visita',
			'tempo_visita',
			'orario_ricovero',
			'orario_dimissioni',
			'luogo_intervento',
			'indirizzo_intervento',
			'nome_struttura',
			'piano',
			'dettagli_scale',
			'dettagli_ascensore',
			'attrezzatura_precedente',
			'data_nascita',
			'luogo_nascita',
			'codice_fiscale',
			'recapito_telefonico',
			'struttura_da',
			'struttura_a'
		];

		foreach ($fields_to_sanitize as $field) {
			$data[$field] = isset($post_data[$field]) ? sanitize_text_field(wp_unslash($post_data[$field])) : '';
		}

		$data['data_trasporto']   = isset($post_data['data_trasporto']) ? sanitize_text_field(wp_unslash($post_data['data_trasporto'])) : '';
		$data['recapito_email']   = isset($post_data['recapito_email']) ? sanitize_email(wp_unslash($post_data['recapito_email'])) : '';
		$data['indirizzo_intervento'] = isset($post_data['indirizzo_intervento']) ? sanitize_textarea_field(wp_unslash($post_data['indirizzo_intervento'])) : '';
		$data['indirizzo_destinazione'] = isset($post_data['indirizzo_destinazione']) ? sanitize_textarea_field(wp_unslash($post_data['indirizzo_destinazione'])) : '';
		$data['ascensore']        = isset($post_data['ascensore']) ? sanitize_text_field(wp_unslash($post_data['ascensore'])) : 'assente';
		$data['trasporto_precedente'] = isset($post_data['trasporto_precedente']) && $post_data['trasporto_precedente'] === 'si' ? 'Sì' : 'No';

		return $data;
	}

	/**
	 * Inserisce la richiesta nel database.
	 *
	 * @param array $data Dati sanificati da inserire.
	 * @return int|false L'ID della riga inserita o false in caso di errore.
	 */
	private function insert_request_into_db(array $data): int|false
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'crive_transport_requests';

		// Genera un token amministrativo univoco per questa richiesta
		$admin_token = bin2hex(random_bytes(32));
		$data['_admin_token'] = $admin_token;

		$db_data = [
			'created_at'          => current_time('mysql'),
			'nome_cognome'        => $data['nome_cognome'],
			'data_trasporto'      => $data['data_trasporto'],
			'recapito_telefonico' => $data['recapito_telefonico'],
			'recapito_email'      => $data['recapito_email'],
			'motivo_trasporto'    => $data['motivo_trasporto'],
			'luogo_intervento'    => $data['luogo_intervento'],
			'indirizzo_intervento' => $data['indirizzo_intervento'],
			'piano'               => $data['piano'],
			'ascensore'           => ($data['ascensore'] === 'presente' ? 1 : 0),
			'larghezza_scale'     => $data['dettagli_scale'],
			'codice_fiscale'      => $data['codice_fiscale'],
			'dettagli_trasporto'  => wp_json_encode($data),
			'status'              => RequestStatus::Pending->value,
		];

		$result = $wpdb->insert($table_name, $db_data);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Verifica se un token è valido per una data richiesta.
	 *
	 * @param int $request_id
	 * @param string $token
	 * @return bool
	 */
	public function verify_token(int $request_id, string $token): bool
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'crive_transport_requests';
		$json_data = $wpdb->get_var($wpdb->prepare("SELECT dettagli_trasporto FROM $table_name WHERE id = %d", $request_id));

		if (!$json_data) {
			return false;
		}

		$data = json_decode($json_data, true);
		return isset($data['_admin_token']) && hash_equals($data['_admin_token'], $token);
	}

	/**
	 * Aggiorna lo stato di una richiesta tramite token (senza auth WP).
	 *
	 * @param int $request_id
	 * @param string $token
	 * @param RequestStatus $new_status
	 * @return bool|WP_Error
	 */
	public function update_status_by_token(int $request_id, string $token, RequestStatus $new_status): bool|WP_Error
	{
		if (!$this->verify_token($request_id, $token)) {
			return new WP_Error('invalid_token', 'Token non valido o scaduto.');
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'crive_transport_requests';

		$updated = $wpdb->update(
			$table_name,
			['status' => $new_status->value],
			['id' => $request_id]
		);

		return $updated !== false;
	}

	/**
	 * Genera l'URL per un'azione pubblica.
	 *
	 * @param int $request_id
	 * @param array $data Assicurati che contenga _admin_token
	 * @param string $action 'processing' o 'confirmed'
	 * @return string
	 */
	private function generate_action_url(int $request_id, array $data, string $action): string
	{
		$token = $data['_admin_token'] ?? '';
		if (!$token) return '';

		return add_query_arg([
			'crive_action' => 'update_status',
			'rid' => $request_id,
			'token' => $token,
			'status' => $action
		], home_url('/'));
	}

	/**
	 * Invia le email di notifica.
	 *
	 * @param array $data Dati sanificati del form.
	 * @param int $request_id ID della richiesta inserita nel DB.
	 * @return bool True se entrambi gli invii (o almeno quello admin) sono andati a buon fine, false altrimenti.
	 */
	private function send_notifications(array $data, int $request_id): bool
	{
		$user_email    = $data['recapito_email'];
		$notification_email = get_option('crive_notification_email');
		// Assicura che l'email admin di fallback sia valida
		$default_admin_email = get_option('admin_email');
		$admin_email   = ! empty($notification_email) && is_email($notification_email) ? $notification_email : (is_email($default_admin_email) ? $default_admin_email : null);

		$headers       = ['Content-Type: text/html; charset=UTF-8'];
		$user_sent     = false;
		$admin_sent    = false;

		// 1. Email di conferma all'utente
		$user_subject = esc_html__('Conferma Ricezione Richiesta di Trasporto', 'cri-trasporti');
		$user_body    = '<p>' . sprintf(esc_html__('Gentile %s,', 'cri-trasporti'), esc_html($data['nome_cognome'])) . '</p>';
		$user_body   .= '<p>' . esc_html__('La tua richiesta di trasporto è stata ricevuta correttamente e verrà elaborata al più presto. A breve verrai ricontattato da un nostro operatore per la conferma definitiva.', 'cri-trasporti') . '</p>';
		$user_body   .= '<p>' . esc_html__('Riepilogo richiesta:', 'cri-trasporti') . '</p>';
		$user_body   .= '<ul>';
		$user_body   .= '<li><strong>' . esc_html__('ID Richiesta:', 'cri-trasporti') . '</strong> ' . $request_id . '</li>';
		$user_body   .= '<li><strong>' . esc_html__('Data Trasporto:', 'cri-trasporti') . '</strong> ' . esc_html($data['data_trasporto']) . '</li>';
		$user_body   .= '</ul>';
		$user_body   .= '<p><strong>' . esc_html__('Croce Rossa Italiana - Comitato di Venezia', 'cri-trasporti') . '</strong></p>';

		if (is_email($user_email)) {
			$user_sent = wp_mail($user_email, $user_subject, $user_body, $headers);
			if (! $user_sent) {
				error_log('CRIVE Trasporti: Fallito invio email conferma utente per richiesta ID ' . $request_id . ' a ' . $user_email);
			}
		} else {
			error_log('CRIVE Trasporti: Email utente non valida per richiesta ID ' . $request_id . ': ' . $user_email);
		}


		// 2. Email di notifica alla segreteria
		if (! $admin_email) {
			error_log('CRIVE Trasporti: Nessuna email valida configurata per le notifiche admin.');
			return $user_sent;
		}

		$admin_subject = sprintf(esc_html__('Nuova Richiesta di Trasporto #%d', 'cri-trasporti'), $request_id);
		$admin_body    = '<h1>' . sprintf(esc_html__('Nuova Richiesta di Trasporto #%d', 'cri-trasporti'), $request_id) . '</h1>';
		
		// Pulsanti Azioni Rapide
		$url_processing = $this->generate_action_url($request_id, $data, 'processing');
		$url_confirmed = $this->generate_action_url($request_id, $data, 'confirmed');

		$admin_body .= '<div style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd; margin-bottom: 20px; border-radius: 5px;">';
		$admin_body .= '<h3 style="margin-top: 0;">' . esc_html__('Azioni Rapide', 'cri-trasporti') . '</h3>';
		$admin_body .= '<p style="margin-bottom: 15px;">' . esc_html__('Puoi cambiare lo stato della richiesta cliccando sui pulsanti qui sotto, senza effettuare il login.', 'cri-trasporti') . '</p>';
		$admin_body .= '<div>';
		$admin_body .= '<a href="' . esc_url($url_processing) . '" style="background-color: #3b82f6; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px; font-weight: bold;">' . esc_html__('Prendi in Carico', 'cri-trasporti') . '</a>';
		$admin_body .= '<a href="' . esc_url($url_confirmed) . '" style="background-color: #10b981; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold;">' . esc_html__('Conferma Trasporto', 'cri-trasporti') . '</a>';
		$admin_body .= '</div></div>';

		$admin_body   .= '<p>' . esc_html__('Di seguito i dettagli della richiesta:', 'cri-trasporti') . '</p>';

		$admin_body .= '<ul>';
		foreach ($data as $key => $value) {
			if (str_starts_with($key, '_')) continue; // Nascondi token e campi interni
			if (isset($value) && $value !== '') { 
				$label = ucwords(str_replace('_', ' ', $key));
				$display_value = is_array($value) ? esc_html(wp_json_encode($value)) : esc_html((string) $value);

				if ($key === 'ascensore') {
					$display_value = ucfirst($value); 
				}

				$admin_body .= '<li><strong>' . esc_html($label) . ':</strong> ' . $display_value . '</li>';
			}
		}
		$admin_body .= '</ul>';

		// Genera e allega il PDF
		$pdf_generator = new PDFGenerator();
		$pdf_path = $pdf_generator->generate($data, $request_id);

		$attachments = [];
		if ($pdf_path && file_exists($pdf_path)) {
			$attachments[] = $pdf_path;
		} else {
			error_log('CRIVE Trasporti: Fallita generazione o accesso al PDF per richiesta ID ' . $request_id);
		}

		$admin_sent = wp_mail($admin_email, $admin_subject, $admin_body, $headers, $attachments);
		if (! $admin_sent) {
			error_log('CRIVE Trasporti: Fallito invio email notifica admin per richiesta ID ' . $request_id . ' a ' . $admin_email);
		}

		if ($pdf_path && file_exists($pdf_path)) {
			wp_delete_file($pdf_path);
		}

		return $admin_sent;
	}
}
