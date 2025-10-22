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
		check_ajax_referer( 'cri_transport_request_nonce', 'security' );

		$result = $this->request_manager->process_new_request( $_POST );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		} else {
			wp_send_json_success( [ 'message' => esc_html__( 'Richiesta inviata con successo! Riceverai a breve una email di conferma.', 'cri-trasporti' ) ] );
		}
	}
}

