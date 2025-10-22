<?php

namespace CRIVenice\Transport\Includes;

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;

// Questo file è ora inteso per essere usato solo da script CLI.

/**
 * Gestisce la compilazione dei file SCSS in CSS (solo per CLI).
 *
 * @since 1.0.0
 */
class SCSSCompiler {

	/**
	 * Compila un file SCSS.
	 *
	 * @param string $scss_file_name Nome del file SCSS (senza estensione).
	 * @param string $plugin_dir     Percorso della directory principale del plugin.
	 * @return void
	 * @throws SassException In caso di errore di sintassi SCSS.
	 * @throws \Exception Se si verificano errori di I/O (file non trovato, permessi, etc.).
	 */
	public function compile( string $scss_file_name, string $plugin_dir ): void {
		$scss_file = rtrim( $plugin_dir, '/' ) . "/assets/scss/{$scss_file_name}.scss";
		$css_file  = rtrim( $plugin_dir, '/' ) . "/assets/css/{$scss_file_name}.css";
		$css_dir   = dirname( $css_file );

		// Controllo 1: Il file sorgente esiste?
		if ( ! file_exists( $scss_file ) ) {
			throw new \Exception( "File SCSS non trovato al percorso: " . $scss_file );
		}

		try {
			// Controllo 2: La cartella di destinazione è scrivibile?
			if ( ! is_dir( $css_dir ) && ! mkdir( $css_dir, 0755, true ) ) {
				throw new \Exception( "Impossibile creare la cartella di destinazione: " . $css_dir );
			}
			if ( ! is_writable( $css_dir ) ) {
				throw new \Exception( "La cartella di destinazione non è scrivibile: " . $css_dir );
			}

			$source = file_get_contents( $scss_file );

			if ( empty( trim( $source ) ) ) {
				throw new \Exception( "Il file SCSS sorgente ({$scss_file_name}.scss) è vuoto." );
			}

			$compiler = new Compiler();
			$compiled = $compiler->compileString( $source )->getCss();
			$write_result = file_put_contents( $css_file, $compiled );

			// Controllo 3: La scrittura è andata a buon fine?
			if ( $write_result === false ) {
				throw new \Exception( "Impossibile scrivere nel file CSS di destinazione: " . $css_file );
			}

		} catch ( SassException $e ) {
			// Rilancia l'eccezione per mostrarla nella console.
			throw $e;
		}
	}
}

