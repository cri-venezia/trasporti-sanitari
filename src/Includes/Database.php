<?php

namespace CriVenice\Transport\Includes;

use CRIVenice\Transport\Enums\RequestStatus;

if ( ! defined(  'ABSPATH') ) {
	exit; // exit if called directly
}

/**
 * Gestisce la creazione e l'aggiornamento della tabella del database.
 *
 * @since 1.0.0
 */
class Database {
	/**
	 * Crea la tabella personalizzata nel database all'attivazione del plugin.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function create_table(): void {
		global $wpdb;
		$table_name = $wpdb->prefix . 'crive_transport_requests';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            nome_cognome tinytext NOT NULL,
            data_trasporto date NOT NULL,
            recapito_telefonico varchar(55) DEFAULT '' NOT NULL,
            recapito_email varchar(100) DEFAULT '' NOT NULL,
            motivo_trasporto varchar(100) DEFAULT '' NOT NULL,
            luogo_intervento varchar(100) DEFAULT '' NOT NULL,
            indirizzo_intervento text NOT NULL,
            piano varchar(50) DEFAULT '' NOT NULL,
            ascensore tinyint(1) DEFAULT 0 NOT NULL,
            larghezza_scale varchar(50) DEFAULT '' NOT NULL,
            codice_fiscale varchar(16) DEFAULT '' NOT NULL,
            dettagli_trasporto longtext NOT NULL,
            status varchar(20) DEFAULT '" . RequestStatus::Pending->value . "' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
