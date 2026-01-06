<?php

/**
 * Plugin Name:       CRIVE Trasporti
 * Plugin URI:        https://crivenezia.it/
 * Description:       Aggiunge un widget Elementor per inviare richieste di trasporto sanitario al Comitato di Venezia della Croce Rossa Italiana.
 * Version:           1.2.1
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Author:            AHDCreative Web Solutions
 * Author URI:        https://ahd-creative.agency/
 * License:           Proprietary
 * Text Domain:       cri-trasporti
 * Domain Path:       /languages
 */

use CRIVenice\Transport\Includes\Database;
use CRIVenice\Transport\Includes\RoleManager;
use CRIVenice\Transport\Plugin;
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Definisce costanti utili per il plugin.
if ( ! defined( 'CRI_TRASPORTI_FILE' ) ) {
	define( 'CRI_TRASPORTI_FILE', __FILE__ );
	define( 'CRI_TRASPORTI_PATH', plugin_dir_path( __FILE__ ) );
	define( 'CRI_TRASPORTI_VERSION', '1.2.1' ); // La versione corrente del plugin
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

// 3. Inizializza il sistema di auto-aggiornamento da GitHub.
try {
	$myUpdateChecker = PucFactory::buildUpdateChecker(
		'https://github.com/cri-venezia/trasporti-sanitari', // <-- SOSTITUISCI CON IL TUO REPO
		CRI_TRASPORTI_FILE, // Percorso del file principale del plugin
		'cri-trasporti' // Lo slug del plugin (nome cartella)
	);

	// Forza l'uso degli asset di release (i file .zip caricati) invece del codice sorgente
	$myUpdateChecker->getVcsApi()->enableReleaseAssets();

	// Opzionale: Imposta il branch da cui controllare gli aggiornamenti (es: 'main', 'master'). Default è 'master'.
	// $myUpdateChecker->setBranch('main');

	// Opzionale: Se il repository è privato, devi fornire un token di accesso.
	// $myUpdateChecker->setAuthentication('IL_TUO_GITHUB_ACCESS_TOKEN');

} catch (LogicException $e) {
	// Gestisce eventuali errori durante l'inizializzazione dell'update checker
	add_action('admin_notices', function() use ($e) {
		echo '<div class="error"><p>Errore nell\'inizializzazione dell\'update checker: ' . esc_html($e->getMessage()) . '</p></div>';
	});
}


// 4. Registra gli hook di attivazione e disattivazione.
register_activation_hook( CRI_TRASPORTI_FILE, [ Plugin::class, 'activate' ] );
register_deactivation_hook( CRI_TRASPORTI_FILE, [ Plugin::class, 'deactivate' ] );


// 5. Inizializza il plugin.
Plugin::instance();
