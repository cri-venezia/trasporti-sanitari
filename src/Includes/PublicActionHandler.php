<?php

namespace CRIVenice\Transport\Includes;

use CRIVenice\Transport\Enums\RequestStatus;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Gestisce le azioni pubbliche tramite URL tokenizzato.
 *
 * @since 1.2.0
 */
class PublicActionHandler
{
	/**
	 * Inizializza gli hook.
	 */
	public function init(): void
	{
		add_action('init', [$this, 'handle_public_actions']);
	}

	/**
	 * Intercetta le azioni pubbliche.
	 */
	public function handle_public_actions(): void
	{
		// Esempio URL: ?crive_action=update_status&rid=123&token=xyz&status=processing
		if (! isset($_GET['crive_action']) || $_GET['crive_action'] !== 'update_status') {
			return;
		}

		$request_id = isset($_GET['rid']) ? absint($_GET['rid']) : 0;
		$token      = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
		$status     = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

		if (! $request_id || ! $token || ! $status) {
			wp_die('Parametri mancanti.', 'CRI Trasporti', ['response' => 400]);
		}

		$manager = new RequestManager();
		
		// Mappa lo string status all'Enum
		$new_status = match($status) {
			'processing' => RequestStatus::Processing,
			'confirmed' => RequestStatus::Confirmed,
			default => null
		};

		if (! $new_status) {
			wp_die('Stato non valido.', 'CRI Trasporti', ['response' => 400]);
		}

		$result = $manager->update_status_by_token($request_id, $token, $new_status);

		if (is_wp_error($result)) {
			wp_die($result->get_error_message(), 'Errore CRI Trasporti', ['response' => 403]);
		}

		// Successo: Mostra pagina semplice
		$this->render_success_page($new_status);
		exit;
	}

	/**
	 * Renderizza una pagina di conferma semplice.
	 *
	 * @param RequestStatus $status
	 */
	private function render_success_page(RequestStatus $status): void
	{
		// Carica header WP base per stili (opzionale, ma rende la pagina meno "spartana")
		// Per semplicità e velocità, usiamo HTML statico pulito con logo e messaggio.
		
		$title = '';
		$message = '';
		$color = '';

		if ($status === RequestStatus::Processing) {
			$title = 'Presa in Carico';
			$message = 'La richiesta è stata presa in carico con successo.';
			$color = '#3b82f6'; // Blu
		} elseif ($status === RequestStatus::Confirmed) {
			$title = 'Trasporto Confermato';
			$message = 'Il trasporto è stato confermato con successo.';
			$color = '#10b981'; // Verde
		}

		?>
		<!DOCTYPE html>
		<html lang="it">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php echo esc_html($title); ?> - CRI Venezia</title>
			<style>
				body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
				.card { background: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); text-align: center; max-width: 400px; width: 100%; }
				.icon { font-size: 3rem; color: <?php echo $color; ?>; margin-bottom: 1rem; }
				h1 { color: #1f2937; margin-bottom: 0.5rem; font-size: 1.5rem; }
				p { color: #4b5563; margin-top: 0; }
				.btn { display: inline-block; margin-top: 1.5rem; padding: 0.5rem 1rem; background: #ef4444; color: white; text-decoration: none; border-radius: 0.25rem; font-weight: 500; }
				.btn:hover { background: #dc2626; }
			</style>
		</head>
		<body>
			<div class="card">
				<div class="icon">✓</div>
				<h1><?php echo esc_html($title); ?></h1>
				<p><?php echo esc_html($message); ?></p>
				<a href="<?php echo esc_url(home_url()); ?>" class="btn">Vai al Sito</a>
			</div>
		</body>
		</html>
		<?php
	}
}
