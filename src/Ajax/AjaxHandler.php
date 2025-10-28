<?php

namespace CRIVenice\Transport\Ajax;

use CRIVenice\Transport\Includes\RequestManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Gestisce tutte le chiamate AJAX del plugin.
 *
 * @since 1.0.0
 */
class AjaxHandler {

	/**
	 * @var RequestManager
	 */
	private RequestManager $request_manager;

	/**
	 * Costruttore.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->request_manager = new RequestManager();

		// Aggancia la funzione di gestione del form sia per utenti loggati che non.
		$action = 'cri_submit_transport_request';
		add_action( 'wp_ajax_' . $action, [ $this, 'handle_transport_request' ] );
		add_action( 'wp_ajax_nopriv_' . $action, [ $this, 'handle_transport_request' ] );
	}

	/**
	 * Gestisce la richiesta di trasporto inviata dal form.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function handle_transport_request(): void {
		// --- NUOVO LOG PER DIAGNOSI ---
		if (defined('WP_DEBUG') && WP_DEBUG) {
			error_log('CRIVE Trasporti AJAX: Inizio gestione richiesta.');
		}

		// 1. Verifica Nonce
		$nonce_check = check_ajax_referer( 'cri_transport_request_nonce', 'security', false );

		if (defined('WP_DEBUG') && WP_DEBUG) {
			error_log('CRIVE Trasporti AJAX: Nonce check result: ' . ($nonce_check ? 'Successo' : 'Fallimento'));
		}

		if ( ! $nonce_check ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Errore di sicurezza (nonce non valido).', 'cri-trasporti' ) ] );
			return;
		}

		// La validazione del nonce è stata bypassata per loggarne il fallimento,
		// ma è essenziale rispondere con errore se fallisce.

		// Continua la logica delegando al RequestManager
		$result = $this->request_manager->process_new_request( $_POST );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		} else {
			wp_send_json_success( [ 'message' => esc_html__( 'Richiesta inviata con successo! Riceverai a breve una email di conferma.', 'cri-trasporti' ) ] );
		}
	}
}
