<?php

namespace CRIVenice\Transport\Includes;

use CRIVenice\Transport\Enums\RequestStatus;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Gestisce la logica di business per la creazione e la gestione delle richieste.
 *
 * @since 1.0.0
 */
class RequestManager {

	/**
	 * Elabora e salva una nuova richiesta di trasporto.
	 *
	 * @param array $post_data Dati grezzi dal form ($_POST).
	 * @return int|WP_Error L'ID della richiesta in caso di successo, altrimenti un oggetto WP_Error.
	 */
	public function process_new_request( array $post_data ): int|WP_Error {
		$sanitized_data = $this->sanitize_and_validate_request_data( $post_data );

		if ( is_wp_error( $sanitized_data ) ) {
			return $sanitized_data;
		}

		$insert_id = $this->insert_request_into_db( $sanitized_data );
		if ( ! $insert_id ) {
			return new WP_Error('db_error', esc_html__( 'Si è verificato un errore durante il salvataggio della richiesta.', 'cri-trasporti' ));
		}

		$this->send_notifications( $sanitized_data, $insert_id );

		return $insert_id;
	}

	/**
	 * Sanifica e valida i dati di una richiesta.
	 *
	 * @param array $post_data Dati grezzi.
	 * @return array|WP_Error Dati sanificati o un errore.
	 */
	private function sanitize_and_validate_request_data( array $post_data ): array|WP_Error {
		$data = [];
		$errors = new WP_Error();

		$required_fields = [
			'nome_cognome'        => esc_html__( 'Nome e Cognome Richiedente', 'cri-trasporti' ),
			'data_trasporto'      => esc_html__( 'Data del Trasporto', 'cri-trasporti' ),
			'recapito_telefonico' => esc_html__( 'Recapito Telefonico', 'cri-trasporti' ),
			'recapito_email'      => esc_html__( 'Recapito Email', 'cri-trasporti' ),
		];

		foreach ( $required_fields as $field => $label ) {
			if ( empty( $post_data[ $field ] ) ) {
				$errors->add( 'field_required', sprintf( esc_html__( 'Il campo "%s" è obbligatorio.', 'cri-trasporti' ), $label ) );
			}
		}

		if ( ! empty( $post_data['recapito_email'] ) && ! is_email( $post_data['recapito_email'] ) ) {
			$errors->add( 'invalid_email', esc_html__( 'L\'indirizzo email inserito non è valido.', 'cri-trasporti' ) );
		}

		if ( $errors->has_errors() ) {
			return $errors;
		}

		// Sanificazione di tutti i campi
		$fields_to_sanitize = [
			'nome_cognome', 'motivo_trasporto', 'tipologia_visita', 'orario_visita', 'tempo_visita',
			'orario_ricovero', 'orario_dimissioni', 'luogo_intervento', 'indirizzo_intervento',
			'nome_struttura', 'piano', 'larghezza_scale', 'data_nascita', 'luogo_nascita',
			'codice_fiscale', 'recapito_telefonico', 'struttura_da', 'struttura_a'
		];

		foreach( $fields_to_sanitize as $field ) {
			$data[$field] = isset( $post_data[$field] ) ? sanitize_text_field( wp_unslash( $post_data[$field] ) ) : '';
		}

		$data['data_trasporto']   = isset( $post_data['data_trasporto'] ) ? sanitize_text_field( wp_unslash( $post_data['data_trasporto'] ) ) : '';
		$data['recapito_email']   = isset( $post_data['recapito_email'] ) ? sanitize_email( wp_unslash( $post_data['recapito_email'] ) ) : '';
		$data['indirizzo_intervento'] = isset( $post_data['indirizzo_intervento'] ) ? sanitize_textarea_field( wp_unslash( $post_data['indirizzo_intervento'] ) ) : '';
		$data['ascensore']        = isset( $post_data['ascensore'] ) && $post_data['ascensore'] === 'si' ? 1 : 0;

		return $data;
	}

	/**
	 * Inserisce la richiesta nel database.
	 *
	 * @param array $data Dati sanificati da inserire.
	 * @return int|false L'ID della riga inserita o false in caso di errore.
	 */
	private function insert_request_into_db( array $data ): int|false {
		global $wpdb;
		$table_name = $wpdb->prefix . 'crive_transport_requests';

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
			'ascensore'           => $data['ascensore'],
			'larghezza_scale'     => $data['larghezza_scale'],
			'codice_fiscale'      => $data['codice_fiscale'],
			'dettagli_trasporto'  => wp_json_encode($data),
			'status'              => RequestStatus::Pending->value,
		];

		$result = $wpdb->insert($table_name, $db_data);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Invia le email di notifica.
	 *
	 * @param array $data Dati sanificati del form.
	 * @param int $request_id ID della richiesta inserita nel DB.
	 * @return void
	 */
	private function send_notifications( array $data, int $request_id ): void {
		$user_email    = $data['recapito_email'];
		$notification_email = get_option('crive_notification_email');
		$admin_email   = ! empty( $notification_email ) ? $notification_email : get_option( 'admin_email' );

		$headers       = [ 'Content-Type: text/html; charset=UTF-8' ];

		// 1. Email di conferma all'utente
		$user_subject = esc_html__( 'Conferma Ricezione Richiesta di Trasporto', 'cri-trasporti' );
		$user_body    = '<p>' . sprintf( esc_html__( 'Gentile %s,', 'cri-trasporti' ), $data['nome_cognome'] ) . '</p>';
		$user_body   .= '<p>' . esc_html__( 'La tua richiesta di trasporto è stata ricevuta correttamente e verrà elaborata al più presto. A breve verrai ricontattato da un nostro operatore per la conferma definitiva.', 'cri-trasporti' ) . '</p>';
		$user_body   .= '<p>' . esc_html__( 'Riepilogo richiesta:', 'cri-trasporti' ) . '</p>';
		$user_body   .= '<ul>';
		$user_body   .= '<li><strong>' . esc_html__( 'ID Richiesta:', 'cri-trasporti' ) . '</strong> ' . $request_id . '</li>';
		$user_body   .= '<li><strong>' . esc_html__( 'Data Trasporto:', 'cri-trasporti' ) . '</strong> ' . esc_html( $data['data_trasporto'] ) . '</li>';
		$user_body   .= '</ul>';
		$user_body   .= '<p><strong>' . esc_html__( 'Croce Rossa Italiana - Comitato di Venezia', 'cri-trasporti' ) . '</strong></p>';

		wp_mail( $user_email, $user_subject, $user_body, $headers );

		// 2. Email di notifica alla segreteria
		$admin_subject = sprintf( esc_html__( 'Nuova Richiesta di Trasporto #%d', 'cri-trasporti' ), $request_id );
		$admin_body    = '<h1>' . sprintf( esc_html__( 'Nuova Richiesta di Trasporto #%d', 'cri-trasporti' ), $request_id ) . '</h1>';
		$admin_body   .= '<p>' . esc_html__( 'È stata inviata una nuova richiesta di trasporto. Di seguito i dettagli:', 'cri-trasporti' ) . '</p>';

		$admin_body .= '<ul>';
		foreach ( $data as $key => $value ) {
			if ( ! empty( $value ) ) {
				$label = ucwords( str_replace( '_', ' ', $key ) );
				$display_value = is_array( $value ) ? wp_json_encode( $value ) : $value;

				if ( $key === 'ascensore' ) {
					$display_value = $value ? 'Sì' : 'No';
				}

				$admin_body .= '<li><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $display_value ) . '</li>';
			}
		}
		$admin_body .= '</ul>';

		// Genera e allega il PDF
		$pdf_generator = new PDFGenerator();
		$pdf_path = $pdf_generator->generate($data, $request_id);

		$attachments = [];
		if ($pdf_path && file_exists($pdf_path)) {
			$attachments[] = $pdf_path;
		}

		wp_mail( $admin_email, $admin_subject, $admin_body, $headers, $attachments );

		if ($pdf_path && file_exists($pdf_path)) {
			wp_delete_file($pdf_path);
		}
	}
}

