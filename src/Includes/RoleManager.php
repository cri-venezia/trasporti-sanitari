<?php

namespace CRIVenice\Transport\Includes;

use CRIVenice\Transport\Admin\AdminPage;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Gestisce il ruolo personalizzato e le relative capabilities.
 *
 * @since 1.0.0
 */
class RoleManager {

	/**
	 * Lo slug del ruolo personalizzato.
	 */
	public const ROLE_SLUG = 'crive_operatore_trasporti';

	/**
	 * Costruttore.
	 */
	public function __construct() {
		// Questi hook vengono eseguiti solo se l'utente è il nostro operatore
		if ( current_user_can( self::ROLE_SLUG ) && ! current_user_can( 'manage_options' ) ) {
			add_action( 'admin_menu', [ $this, 'hide_admin_menus' ], 999 );
			add_action( 'admin_init', [ $this, 'redirect_non_allowed_pages' ] );
		}
	}

	/**
	 * Registra il ruolo personalizzato e assegna le capabilities.
	 * Da chiamare all'attivazione del plugin.
	 *
	 * @return void
	 */
	public static function register_role(): void {
		// Aggiunge la capability personalizzata
		add_role(
			self::ROLE_SLUG,
			'Operatore Trasporti',
			[
				'read'                 => true,
				AdminPage::CAPABILITY  => true,
			]
		);

		// Aggiunge la capability anche agli amministratori
		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			$admin_role->add_cap( AdminPage::CAPABILITY );
		}
	}

	/**
	 * Rimuove il ruolo personalizzato e le capabilities.
	 * Da chiamare alla disattivazione del plugin.
	 *
	 * @return void
	 */
	public static function remove_role(): void {
		// Rimuove la capability dagli amministratori
		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			$admin_role->remove_cap( AdminPage::CAPABILITY );
		}

		// Rimuove il ruolo personalizzato
		if ( get_role( self::ROLE_SLUG ) ) {
			remove_role( self::ROLE_SLUG );
		}
	}

	/**
	 * Nasconde tutte le voci di menu tranne quelle del plugin e il profilo.
	 *
	 * @return void
	 */
	public function hide_admin_menus(): void {
		global $menu, $submenu;

		// Elenco delle pagine consentite
		$allowed_menus = [
			'crive-transport-requests', // Slug del nostro menu principale
			'profile.php',
		];

		foreach ( $menu as $key => $item ) {
			// $item[2] è lo slug del menu
			if ( ! in_array( $item[2], $allowed_menus, true ) ) {
				remove_menu_page( $item[2] );
			}
		}

		// Nasconde il sottomenu "Bacheca" -> "Home" (che rimane di default)
		if (isset($submenu['crive-transport-requests'])) {
			unset($submenu['crive-transport-requests'][0]);
		}
	}

	/**
	 * Reindirizza l'utente se tenta di accedere a pagine admin non consentite.
	 *
	 * @return void
	 */
	public function redirect_non_allowed_pages(): void {
		// Non bloccare le richieste AJAX
		if ( wp_doing_ajax() ) {
			return;
		}
		
		global $pagenow;

		// Pagine consentite
		$allowed_pages = [
			'admin.php', // Pagina base per i nostri menu
			'profile.php', // Pagina del profilo
		];

		if ( in_array( $pagenow, $allowed_pages, true ) ) {
			// Se è admin.php, controlla che sia una delle nostre pagine
			if ( $pagenow === 'admin.php' && isset($_GET['page']) ) {
				$allowed_slugs = [
					'crive-transport-requests',
					// 'crive-add-new-request', // L'operatore non può vederla
					// 'crive-transport-settings', // L'operatore non può vederla
				];
				if ( in_array( $_GET['page'], $allowed_slugs, true ) ) {
					return; // Pagina consentita
				}
			} elseif ( $pagenow === 'profile.php' ) {
				return; // Pagina profilo consentita
			}
		}

		// Se non è una pagina consentita, reindirizza alla nostra pagina principale
		if ( $pagenow !== 'admin.php' || !isset($_GET['page']) || !in_array( $_GET['page'], ['crive-transport-requests'], true ) ) {
			wp_redirect( admin_url( 'admin.php?page=crive-transport-requests' ) );
			exit;
		}
	}
}
