<?php

namespace CRIVenice\Transport\Admin;

use CRIVenice\Transport\Enums\RequestStatus;
use WP_List_Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Gestisce la tabella delle richieste di trasporto nella pagina di amministrazione.
 *
 * @since 1.0.0
 */
class RequestsListTable extends WP_List_Table {

	/**
	 * Costruttore.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct( [
			'singular' => 'request',
			'plural'   => 'requests',
			'ajax'     => false,
		] );
	}

	/**
	 * Definisce le colonne della tabella.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_columns(): array {
		return [
			'cb'             => '<input type="checkbox" />',
			'nome_cognome'   => esc_html__( 'Nome Richiedente', 'cri-trasporti' ),
			'data_trasporto' => esc_html__( 'Data Trasporto', 'cri-trasporti' ),
			'motivo_trasporto' => esc_html__( 'Motivo Trasporto', 'cri-trasporti' ),
			'status'         => esc_html__( 'Stato', 'cri-trasporti' ),
			'created_at'     => esc_html__( 'Data Richiesta', 'cri-trasporti' ),
			'actions'        => esc_html__( 'Azioni', 'cri-trasporti' ),
		];
	}

	/**
	 * Definisce le colonne ordinabili.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function get_sortable_columns(): array {
		return [
			'nome_cognome'   => [ 'nome_cognome', false ],
			'data_trasporto' => [ 'data_trasporto', false ],
			'created_at'     => [ 'created_at', true ], // Ordinamento predefinito
			'status'         => [ 'status', false ],
		];
	}

	/**
	 * Prepara gli elementi da visualizzare nella tabella.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function prepare_items(): void {
		global $wpdb;

		$table_name = $wpdb->prefix . 'crive_transport_requests';
		$per_page   = 20;

		$this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];

		$current_page = $this->get_pagenum();
		$total_items  = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name" );

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
		] );

		$orderby = ( ! empty( $_REQUEST['orderby'] ) && array_key_exists( $_REQUEST['orderby'], $this->get_sortable_columns() ) ) ? sanitize_sql_orderby( $_REQUEST['orderby'] ) : 'created_at';
		$order   = ( ! empty( $_REQUEST['order'] ) && in_array( strtoupper( $_REQUEST['order'] ), [ 'ASC', 'DESC' ] ) ) ? strtoupper( $_REQUEST['order'] ) : 'DESC';

		$offset = ( $current_page - 1 ) * $per_page;

		$this->items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d",
				$per_page,
				$offset
			), ARRAY_A
		);
	}

	/**
	 * Definisce il rendering di default per le colonne.
	 *
	 * @param array $item
	 * @param string $column_name
	 * @return string
	 * @since 1.0.0
	 */
	protected function column_default( $item, $column_name ): string {
		return match ( $column_name ) {
			'nome_cognome' => esc_html( $item['nome_cognome'] ),
			'data_trasporto' => esc_html( wp_date( get_option( 'date_format' ), strtotime( $item[ $column_name ] ) ) ),
			'created_at' => esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item[ $column_name ] ) ) ),
			'motivo_trasporto' => esc_html( $item['motivo_trasporto'] ),
			'status' => RequestStatus::from($item['status'])->label(),
			default => '',
		};
	}

	/**
	 * Definisce il rendering per la colonna delle azioni.
	 *
	 * @param array $item
	 * @return string
	 */
	protected function column_actions($item): string {
		$page_slug = 'crive-transport-requests';
		$actions = [];

		// Pulsante Visualizzazione Rapida (JS)
		// codifichiamo i dettagli in JSON per il data-attribute
		$details_json = $item['dettagli_trasporto'] ?? '{}';
		// Assicuriamoci che sia un JSON valido, altrimenti defaultiamo a oggetto vuoto
		if (empty($details_json)) $details_json = '{}';
		
		$actions[] = sprintf(
			'<button type="button" class="button button-secondary view-details-btn" data-details="%s" title="%s"><span class="dashicons dashicons-visibility" style="margin-top: 3px;"></span></button>',
			esc_attr($details_json),
			esc_attr__('Vedi Dettagli Rapidi', 'cri-trasporti')
		);

		// Pulsante Modifica
		if (current_user_can('manage_options')) {
			$edit_url = admin_url("admin.php?page={$page_slug}&action=edit_request&request_id=" . $item['id']);
			$actions[] = sprintf(
				'<a href="%s" class="button button-secondary" title="%s"><span class="dashicons dashicons-edit" style="margin-top: 3px;"></span></a>',
				esc_url($edit_url),
				esc_attr__('Modifica Richiesta', 'cri-trasporti')
			);
		}

		// Pulsante Vedi PDF
		$view_pdf_url = wp_nonce_url(admin_url("admin.php?page={$page_slug}&action=view_pdf&request_id=" . $item['id']), 'crive_view_pdf_' . $item['id']);
		$actions[] = sprintf('<a href="%s" class="button button-secondary" target="_blank" title="%s">%s</a>', esc_url($view_pdf_url), esc_attr__('Scarica PDF', 'cri-trasporti'), esc_html__('PDF', 'cri-trasporti'));

		// Pulsante Conferma (condizionale)
		if ($item['status'] === RequestStatus::Pending->value) {
			$confirm_url = wp_nonce_url(admin_url("admin.php?page={$page_slug}&action=confirm_request&request_id=" . $item['id']), 'crive_confirm_' . $item['id']);
			$actions[] = sprintf('<a href="%s" class="button button-primary">%s</a>', esc_url($confirm_url), esc_html__('Conferma', 'cri-trasporti'));
		}

		// Pulsante Cancella
		$delete_url = wp_nonce_url(admin_url("admin.php?page={$page_slug}&action=delete_request&request_id=" . $item['id']), 'crive_delete_' . $item['id']);
		$actions[] = sprintf('<a href="%s" class="button button-link-delete">%s</a>', esc_url($delete_url), esc_html__('Cancella', 'cri-trasporti'));

		return implode(' ', $actions);
	}

	/**
	 * Definisce il rendering per la colonna checkbox.
	 *
	 * @param array $item
	 * @return string
	 * @since 1.0.0
	 */
	protected function column_cb( $item ): string {
		return sprintf(
			'<input type="checkbox" name="request[]" value="%s" />', $item['id']
		);
	}
}

