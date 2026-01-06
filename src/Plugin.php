<?php

namespace CRIVenice\Transport;

use CRIVenice\Transport\Admin\AdminPage;
use CRIVenice\Transport\Admin\SettingsPage;
use CRIVenice\Transport\Ajax\AjaxHandler;
use CRIVenice\Transport\Includes\Assets;
use CRIVenice\Transport\Includes\Cron;
use CRIVenice\Transport\Includes\Elementor\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Classe principale del plugin.
 *
 * @since 1.0.0
 */
final class Plugin {

	/**
	 * L'unica istanza della classe.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	public readonly Assets $assets;
	public readonly AjaxHandler $ajax;
	public readonly Elementor $elementor;
	public readonly AdminPage $admin_page;
	public readonly Cron $cron;
	public readonly SettingsPage $settings_page;


	public static function instance(): Plugin {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
		add_action( 'init', [ $this, 'init' ] );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain(
			'cri-trasporti',
			false,
			dirname( plugin_basename( CRI_TRASPORTI_FILE ) ) . '/languages'
		);
	}

	public function init(): void {
		$this->assets     = new Assets();
		$this->ajax       = new AjaxHandler();
		$this->elementor  = new Elementor();
		$this->admin_page = new AdminPage();
		$this->cron       = new Cron();
		$this->settings_page = new SettingsPage();
		$this->public_action_handler = new PublicActionHandler(); // Instantiated PublicActionHandler
		$this->public_action_handler->init(); // Called init method on PublicActionHandler
	}

	public static function deactivate(): void {
		wp_clear_scheduled_hook( Cron::CRON_HOOK );
	}
}
