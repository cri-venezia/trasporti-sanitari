<?php

namespace CRIVenice\Transport\Includes\Elementor;

use Elementor\Elements_Manager;
use Elementor\Widgets_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Gestisce l'integrazione con Elementor.
 *
 * @since 1.0.0
 */
class Elementor {

	/**
	 * Costruttore.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Controlla se Elementor è attivo
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		// Aggiunge i nostri widget personalizzati
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		// Aggiunge la nostra categoria personalizzata
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_widget_category' ] );
	}

	/**
	 * Registra la categoria personalizzata per i widget.
	 *
	 * @param Elements_Manager $elements_manager
	 * @return void
	 * @since 1.0.0
	 */
	public function register_widget_category( Elements_Manager $elements_manager ): void {
		$elements_manager->add_category(
			'cri-venice',
			[
				'title' => esc_html__( 'Croce Rossa Italiana - Venezia', 'cri-trasporti' ),
				'icon'  => 'eicon-folder',
			]
		);
	}

	/**
	 * Registra i widget.
	 *
	 * @param Widgets_Manager $widgets_manager
	 * @return void
	 * @since 1.0.0
	 */
	public function register_widgets( Widgets_Manager $widgets_manager ): void {
		// Assicura che il file del widget sia caricato
		require_once __DIR__ . '/Widgets/TransportFormWidget.php';

		// Registra il widget
		$widgets_manager->register( new Widgets\TransportFormWidget() );
	}
}

