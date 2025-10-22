<?php

namespace CRIVenice\Transport\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Gestisce la registrazione e il caricamento degli script e degli stili.
 *
 * @since 1.0.0
 */
class Assets {

	/**
	 * Costruttore.
	 */
	public function __construct() {
		// Usa gli hook specifici di Elementor per garantire il corretto caricamento.
		add_action( 'elementor/frontend/after_register_styles', [ $this, 'register_styles' ] );
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_scripts' ] );
	}

	/**
	 * Registra gli stili del plugin.
	 *
	 * @return void
	 */
	public function register_styles(): void {
		wp_register_style(
			'crive-form-style',
			plugin_dir_url( CRI_TRASPORTI_FILE ) . 'assets/css/form-style.css',
			[],
			CRI_TRASPORTI_VERSION
		);
	}

	/**
	 * Registra gli script del plugin.
	 *
	 * @return void
	 */
	public function register_scripts(): void {
		wp_register_script(
			'crive-form-handler',
			plugin_dir_url( CRI_TRASPORTI_FILE ) . 'assets/js/form-handler.js',
			[], // Dipendenze
			CRI_TRASPORTI_VERSION,
			true // Nel footer
		);

		// Passa i dati da PHP a JavaScript in modo sicuro.
		wp_localize_script(
			'crive-form-handler',
			'crive_form_data',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'cri_transport_request_nonce' ),
			]
		);
	}
}
