<?php

namespace CRIVenice\Transport\Admin;

use CRIVenice\Transport\Enums\RequestStatus;
use CRIVenice\Transport\Includes\PDFGenerator;
use CRIVenice\Transport\Includes\RequestManager;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Gestisce la pagina di amministrazione delle richieste di trasporto.
 *
 * @since 1.0.0
 */
class AdminPage {

	/**
	 * @var RequestsListTable
	 */
	private RequestsListTable $requests_table;

	/**
	 * Costruttore.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'handle_actions' ] );
		add_action( 'admin_notices', [ $this, 'show_notices' ] );
		add_action( 'admin_head', [ $this, 'add_admin_styles' ] );
	}

	/**
	 * Aggiunge la voce di menu alla bacheca di WordPress.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function add_admin_menu(): void {
		$main_slug = 'crive-transport-requests';
		$icon_svg = 'data:image/svg+xml;base64,' . base64_encode(
				'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                <path d="M21.9,10.1c-0.3-0.6-0.8-1-1.4-1.2l-2.6-0.9L16.2,3c-0.3-0.8-1-1.3-1.8-1.3h-5c-0.8,0-1.5,0.5-1.8,1.3L6.1,8.1L3.5,9 C2.9,9.2,2.4,9.6,2.1,10.1c-0.3,0.6-0.4,1.2-0.1,1.8l1.3,4.2c0.2,0.6,0.7,1.1,1.3,1.3l2.6,0.9l1.8,4.9c0.3,0.8,1,1.3,1.8,1.3h5 c0.8,0,1.5-0.5,1.8-1.3l1.8-4.9l2.6-0.9c0.6-0.2,1.1-0.7,1.3-1.3l1.3-4.2C22.3,11.3,22.2,10.7,21.9,10.1z M14.5,14h-2v2h-1v-2h-2 v-1h2v-2h1v2h2V14z"/>
            </svg>'
			);

		// Crea la pagina principale
		add_menu_page(
			esc_html__( 'Richieste Trasporto', 'cri-trasporti' ),
			esc_html__( 'Richieste Trasporto', 'cri-trasporti' ),
			'edit_others_posts', // Capability per vedere l'elenco
			$main_slug,
			[ $this, 'render_list_page' ],
			$icon_svg,
			20
		);

		// Aggiunge la sotto-pagina "Aggiungi Nuova" (solo admin)
		add_submenu_page(
			$main_slug,
			esc_html__( 'Aggiungi Nuova Richiesta', 'cri-trasporti' ),
			esc_html__( 'Aggiungi Nuova', 'cri-trasporti' ),
			'manage_options', // Capability solo per amministratori
			'crive-add-new-request',
			[ $this, 'render_add_new_page' ]
		);

		// NOTA: La pagina "Impostazioni" viene aggiunta dalla classe SettingsPage.php
	}

	/**
	 * Renderizza il contenuto della pagina di elenco delle richieste.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function render_list_page(): void {
		$this->requests_table = new RequestsListTable();
		$this->requests_table->prepare_items();

		echo '<div class="wrap">';
		echo '<h1>' . esc_html( get_admin_page_title() ) . ' <a href="' . esc_url(admin_url('admin.php?page=crive-add-new-request')) . '" class="page-title-action">' . esc_html__('Aggiungi Nuova', 'cri-trasporti') . '</a></h1>';

		echo '<form method="post">';
		$this->requests_table->display();
		echo '</form>';

		echo '</div>';
	}

	/**
	 * Renderizza il form per aggiungere una nuova richiesta.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function render_add_new_page(): void {
		// Il form è in un file separato per pulizia
		include __DIR__ . '/views/add-new-request-form.php';
	}

	/**
	 * Aggiunge stili CSS personalizzati all'head della pagina admin.
	 */
	public function add_admin_styles(): void {
		$screen = get_current_screen();
		// Assicura che lo stile venga applicato a tutte le pagine del plugin
		if ( $screen && (str_starts_with($screen->id, 'toplevel_page_crive-transport-requests') || str_starts_with($screen->id, 'richieste-trasporto_page_')) ) {
			echo '<style>
				.wp-list-table .column-actions { width: 240px; }
				.column-actions .button { margin-right: 5px; }
				.button-link-delete {
				    color: #dc3232 !important;
				    border-color: #dc3232 !important;
				    background: #f1f1f1 !important;
				    vertical-align: middle;
				}
				.button-link-delete:hover {
				    color: #fff !important;
				    background-color: #dc3232 !important;
				}
			</style>';
		}
	}


	/**
	 * Gestisce le azioni della pagina.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function handle_actions(): void {
		// Gestione invio form "Aggiungi Nuova"
		if ( isset( $_POST['crive_add_new_request_nonce'] ) && wp_verify_nonce( $_POST['crive_add_new_request_nonce'], 'crive_add_new_request' ) ) {
			$this->handle_add_new_submission();
		}

		// Gestione azioni dalla tabella
		if ( ! isset( $this->requests_table ) ) {
			// Istanzia la tabella per poter accedere a current_action()
			$this->requests_table = new RequestsListTable();
		}

		$action = $this->requests_table->current_action();
		if (!$action) return;

		match ($action) {
			'view_pdf' => $this->handle_view_pdf(),
			'confirm_request' => $this->handle_confirm_request(),
			'delete_request' => $this->handle_delete_request(),
			default => null,
		};
	}

	/**
	 * Gestisce l'invio del form per una nuova richiesta dall'admin.
	 */
	private function handle_add_new_submission(): never {
		$manager = new RequestManager();
		$result = $manager->process_new_request($_POST);

		if (is_wp_error($result)) {
			$message = $result->get_error_message();
			set_transient('crive_admin_notice', ['type' => 'error', 'message' => $message]);
		} else {
			$message = sprintf(esc_html__('Richiesta #%d creata con successo.', 'cri-trasporti'), $result);
			set_transient('crive_admin_notice', ['type' => 'success', 'message' => $message]);
		}

		// Reindirizza alla pagina elenco per evitare reinvio del form
		wp_redirect(admin_url('admin.php?page=crive-transport-requests'));
		exit;
	}

	/**
	 * Gestisce la richiesta di visualizzazione del PDF.
	 */
	private function handle_view_pdf(): never {
		$request_id = isset($_GET['request_id']) ? absint($_GET['request_id']) : 0;
		check_admin_referer('crive_view_pdf_' . $request_id);

		if ( ! current_user_can('edit_others_posts') || ! $request_id ) {
			wp_die( esc_html__( 'Azione non permessa.', 'cri-trasporti' ) );
		}

		$request = $this->get_request_by_id($request_id);
		if ( ! $request ) {
			wp_die( esc_html__( 'Richiesta non trovata.', 'cri-trasporti' ) );
		}

		$data = json_decode($request->dettagli_trasporto, true);
		if ( ! is_array($data) ) {
			wp_die( esc_html__( 'I dati della richiesta sono corrotti.', 'cri-trasporti' ) );
		}

		$pdf_generator = new PDFGenerator();
		$pdf_generator->stream($data, $request->id);
		die(); // Termina l'esecuzione dopo aver inviato il PDF
	}

	/**
	 * Gestisce la richiesta di conferma del trasporto.
	 */
	private function handle_confirm_request(): never {
		global $wpdb; // Aggiunta la dichiarazione globale mancante

		$request_id = isset($_GET['request_id']) ? absint($_GET['request_id']) : 0;
		check_admin_referer('crive_confirm_' . $request_id);

		if ( ! current_user_can('edit_others_posts') || ! $request_id ) {
			wp_die( esc_html__( 'Azione non permessa.', 'cri-trasporti' ) );
		}

		$request = $this->get_request_by_id($request_id);
		// Se la richiesta non esiste o è già confermata, reindirizza senza fare nulla.
		if ( ! $request || $request->status === RequestStatus::Confirmed->value ) {
			wp_redirect( admin_url('admin.php?page=crive-transport-requests') );
			exit;
		}

		// Controlla se l'email utente è valida prima di tentare l'invio
		if ( ! is_email( $request->recapito_email ) ) {
			set_transient('crive_admin_notice', ['type' => 'error', 'message' => esc_html__('Impossibile inviare email: L\'indirizzo email del richiedente non è valido.', 'cri-trasporti')]);
			error_log('CRIVE Trasporti: Impossibile inviare email di conferma per richiesta ID ' . $request_id . '. Email richiedente non valida: ' . $request->recapito_email);
			wp_redirect( admin_url('admin.php?page=crive-transport-requests') );
			exit;
		}

		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
		$subject = esc_html__('Conferma Trasporto Sanitario - CRI Venezia', 'cri-trasporti');
		$body = '<p>' . sprintf(esc_html__('Gentile %s,', 'cri-trasporti'), esc_html($request->nome_cognome)) . '</p>';
		$body .= '<p>' . sprintf(esc_html__('Siamo lieti di confermare che il suo trasporto per il giorno %s è stato accettato e pianificato.', 'cri-trasporti'), wp_date(get_option('date_format'), strtotime($request->data_trasporto))) . '</p>';
		$body .= '<p>' . esc_html__('Un nostro operatore la contatterà a breve per i dettagli finali.', 'cri-trasporti') . '</p>';
		$body .= '<p>' . esc_html__('Cordiali saluti,', 'cri-trasporti') . '<br><strong>' . esc_html__('Croce Rossa Italiana - Comitato di Venezia', 'cri-trasporti') . '</strong></p>';

		$sent = wp_mail($request->recapito_email, $subject, $body, $headers);

		if ($sent) {
			$table_name = $wpdb->prefix . 'crive_transport_requests';
			$wpdb->update($table_name, ['status' => RequestStatus::Confirmed->value], ['id' => $request_id]);
			set_transient('crive_admin_notice', ['type' => 'success', 'message' => esc_html__('Email di conferma inviata e richiesta aggiornata.', 'cri-trasporti')]);
		} else {
			set_transient('crive_admin_notice', ['type' => 'error', 'message' => esc_html__('Errore durante l\'invio dell\'email di conferma.', 'cri-trasporti')]);
			error_log('CRIVE Trasporti: Invio email di conferma fallito per richiesta ID ' . $request_id . ' a ' . $request->recapito_email);
		}

		wp_redirect( admin_url('admin.php?page=crive-transport-requests') );
		exit;
	}

	/**
	 * Gestisce la richiesta di cancellazione del trasporto.
	 */
	private function handle_delete_request(): never {
		$request_id = isset($_GET['request_id']) ? absint($_GET['request_id']) : 0;
		check_admin_referer('crive_delete_' . $request_id);

		if ( ! current_user_can('edit_others_posts') || ! $request_id ) {
			wp_die( esc_html__( 'Azione non permessa.', 'cri-trasporti' ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'crive_transport_requests';

		$result = $wpdb->delete($table_name, ['id' => $request_id], ['%d']);

		if ($result) {
			set_transient('crive_admin_notice', ['type' => 'success', 'message' => esc_html__('Richiesta cancellata con successo.', 'cri-trasporti')]);
		} else {
			set_transient('crive_admin_notice', ['type' => 'error', 'message' => esc_html__('Errore durante la cancellazione della richiesta.', 'cri-trasporti')]);
		}

		wp_redirect( admin_url('admin.php?page=crive-transport-requests') );
		exit;
	}

	/**
	 * Mostra i messaggi di notifica (transient-based).
	 *
	 * @return void
	 */
	public function show_notices(): void {
		if ( $notice = get_transient('crive_admin_notice') ) {
			$class = esc_attr($notice['type']);
			$message = esc_html($notice['message']);
			echo "<div class='notice notice-{$class} is-dismissible'><p>{$message}</p></div>";
			delete_transient('crive_admin_notice');
		}
	}

	/**
	 * Recupera una singola richiesta dal DB.
	 *
	 * @param int $id
	 * @return object|null
	 */
	private function get_request_by_id(int $id): ?object {
		global $wpdb;
		$table_name = $wpdb->prefix . 'crive_transport_requests';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id ) );
	}
}
