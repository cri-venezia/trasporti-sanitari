<?php

/**
 * Plugin Name:       CRIVE Trasporti
 * Plugin URI:        https://crivenezia.it/
 * Description:       Aggiunge un widget Elementor per inviare richieste di trasporto sanitario al Comitato di Venezia della Croce Rossa Italiana.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Author:            AHDCreative Web Solutions
 * Author URI:        https://ahd-creative.agency/
 * License:           Proprietary
 * Text Domain:       cri-trasporti
 * Domain Path:       /languages
 */

use CRIVenice\Transport\Includes\Database;
use CRIVenice\Transport\Plugin;
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Definisce costanti utili per il plugin.
if ( ! defined( 'CRI_TRASPORTI_FILE' ) ) {
	define( 'CRI_TRASPORTI_FILE', __FILE__ );
	define( 'CRI_TRASPORTI_PATH', plugin_dir_path( __FILE__ ) );
	define( 'CRI_TRASPORTI_VERSION', '1.0.0' );
}

// 2. Carica l'autoloader di Composer.
$autoloader = CRI_TRASPORTI_PATH . 'vendor/autoload.php';
if ( ! file_exists( $autoloader ) ) {
	add_action( 'admin_notices', static function(): void {
		$message = sprintf(
		/* translators: 1: Plugin name 2: Composer command */
			esc_html__( '"%1$s" error: Please run "%2$s" to install dependencies.', 'cri-trasporti' ),
			'<strong>' . esc_html__( 'CRIVE Trasporti', 'cri-trasporti' ) . '</strong>',
			'<code>composer install</code>'
		);
		echo '<div class="error"><p>' . wp_kses_post( $message ) . '</p></div>';
	} );
	return;
}
require_once $autoloader;

// 3. Inizializza il sistema di auto-aggiornamento da GitHub
try {
	$myUpdateChecker = PucFactory::buildUpdateChecker(
		'https://github.com/cri-venezia/trasporti-sanitari',
		CRI_TRASPORTI_FILE,
		'cri-trasporti'
	);

	// Opzionale: il branch da cui prelevare l'aggiornamento
	$myUpdateChecker->setBranch('main');

	// Token per accesso - Non necessario al momento
	// $myUpdateChecker->setAuthentication('');
} catch (LogicException $e) {
	// Gestisce eventuali errori durante l'inizializzazione del sistema update checker
	add_action('admin_notices', function () use ($e) {
		echo '<div class="error"><p>Errore inizializzazione update checker:' . esc_html__($e->getMessage()) . '</p></div>';
	});
}

// 4. Registra gli hook di attivazione e disattivazione.
register_activation_hook( CRI_TRASPORTI_FILE, [ Database::class, 'create_table' ] );
register_deactivation_hook( CRI_TRASPORTI_FILE, [ Plugin::class, 'deactivate' ] );


// 5. Inizializza il plugin.
Plugin::instance();
