<?php

namespace CRIVenice\Transport\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Gestisce la pagina delle impostazioni del plugin.
 *
 * @since 1.0.0
 */
class SettingsPage {

	private const OPTION_GROUP = 'crive_transport_settings';
	private const OPTION_NAME = 'crive_notification_email';

	/**
	 * Costruttore.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_submenu_page' ] );
		add_action( 'admin_init', [ $this, 'settings_init' ] );
	}

	/**
	 * Aggiunge la pagina delle impostazioni come sottomenu.
	 */
	public function add_submenu_page(): void {
		add_submenu_page(
			'crive-transport-requests',
			esc_html__( 'Impostazioni Trasporti', 'cri-trasporti' ),
			esc_html__( 'Impostazioni', 'cri-trasporti' ),
			'manage_options',
			'crive-transport-settings',
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Inizializza la Settings API di WordPress.
	 */
	public function settings_init(): void {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_email',
				'default'           => '',
			]
		);

		add_settings_section(
			'crive_notifications_section',
			esc_html__( 'Impostazioni Notifiche', 'cri-trasporti' ),
			null,
			self::OPTION_GROUP
		);

		add_settings_field(
			self::OPTION_NAME,
			esc_html__( 'Email per le notifiche', 'cri-trasporti' ),
			[ $this, 'render_email_field' ],
			self::OPTION_GROUP,
			'crive_notifications_section'
		);
	}

	/**
	 * Renderizza il campo input per l'email.
	 */
	public function render_email_field(): void {
		$email = get_option( self::OPTION_NAME, '' );
		echo '<input type="email" name="' . esc_attr( self::OPTION_NAME ) . '" value="' . esc_attr( $email ) . '" class="regular-text">';
		echo '<p class="description">' . esc_html__( 'Inserisci l\'indirizzo email a cui inviare le notifiche per le nuove richieste. Se vuoto, verrà usata l\'email dell\'amministratore del sito.', 'cri-trasporti' ) . '</p>';
	}

	/**
	 * Renderizza la pagina delle impostazioni.
	 */
	public function render_page(): void {
		echo '<div class="wrap">';
		echo '<h1>' . esc_html( get_admin_page_title() ) . '</h1>';
		echo '<form action="options.php" method="post">';

		settings_fields( self::OPTION_GROUP );
		do_settings_sections( self::OPTION_GROUP );
		submit_button();

		echo '</form>';
		echo '</div>';
	}
}
