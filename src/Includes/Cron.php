<?php

namespace CRIVenice\Transport\Includes;

if (! defined('ABSPATH')) {
	exit; // exit if called directly
}

/**
 * Gestisce le attività di pianificate del plugin tramite WP-Cron.
 *
 * @since 1.0.0
 */
class Cron
{

	/**
	 * Identificazione del nostro sistema cron.
	 */
	public const CRON_HOOK = 'crive_cleanup_pdfs_hook';

	/**
	 * Costruttore
	 */
	public function __construct()
	{
		$this->setup_cron();
	}

	/**
	 * Impostiamo il cron job.
	 */
	private function setup_cron(): void
	{
		// Agganciamo la nostra funzione al WP-Cron
		add_action(self::CRON_HOOK, [$this, 'cleanup_old_pdfs']);

		// Pianifica l'evento se non è già stato pianificato
		if (! wp_next_scheduled(self::CRON_HOOK)) {
			// Lo pianifica per eseguirsi ogni giorno, a partire da ora.
			wp_schedule_event(time(), 'daily', self::CRON_HOOK);
		}
	}

	/**
	 * Funzione che pulisce i pdf orfani.
	 *
	 * @return void
	 */
	public function cleanup_old_pdfs(): void
	{
		$upload_dir = wp_upload_dir();
		$requests_dir = $upload_dir['basedir'] . '/cri-requests';

		if (! is_dir($requests_dir)) {
			return;
		}

		$files = glob($requests_dir . '/*.pdf');
		if (! $files) {
			return;
		}

		$expiration_time = 72 * HOUR_IN_SECONDS; // 72 ore
		$current_time = time();

		foreach ($files as $file) {
			if (is_file($file) && ($current_time - filemtime($file)) > $expiration_time) {
				wp_delete_file($file);
			}
		}
	}
}
