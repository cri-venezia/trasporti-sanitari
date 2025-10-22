<?php

namespace CRIVenice\Transport\Includes;

use TCPDF;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Classe personalizzata per estendere TCPDF e definire header/footer.
 */
class CriveTCPDF extends TCPDF {
	public string $primary_logo_url = 'https://crive.b-cdn.net/wp-content/uploads/2024/09/h60-scuro-1.png';
	public string $fallback_logo_url = ''; // Lasciato vuoto come da richiesta

	public function Header(): void {
		$this->render_logo();

		// Font e Titoli
		$this->SetFont('helvetica', 'B', 16);
		$this->SetXY(156, 42);
		$this->Cell(0, 28, 'Modulo di Richiesta Trasporto Sanitario', 0, 2, 'L');
		$this->SetFont('helvetica', '', 12);
		$this->Cell(0, 28, 'Croce Rossa Italiana - Comitato di Venezia', 0, 0, 'L');
		$this->Line(28, 113, 567, 113); // Linea in pixel
	}

	public function Footer(): void {
		$this->SetY(-42);
		$this->SetFont('helvetica', 'I', 8);
		$this->Line(28, $this->GetY() - 14, 567, $this->GetY() - 14);
		$timestamp = wp_date( get_option('date_format') . ' ' . get_option('time_format') );
		$footer_text = sprintf(
			'Documento generato automaticamente dal sito crivenezia.it il %s',
			$timestamp
		);
		$this->Cell(0, 28, $footer_text, 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}

	/**
	 * Renderizza il logo con logica di fallback.
	 */
	private function render_logo(): void {
		$imageData = @file_get_contents($this->primary_logo_url);

		if ( ! $imageData && ! empty($this->fallback_logo_url) ) {
			$imageData = @file_get_contents($this->fallback_logo_url);
		}

		if ($imageData) {
			$this->Image('@' . $imageData, 28, 28, 113, '', 'PNG', '', 'T', false, 300, '', false, false, 0);
		} else {
			$this->SetXY(28, 28);
			$this->Cell(113, 28, 'Logo non disponibile', 1, 0, 'C', 0, '', 0, false, 'T', 'M');
		}
	}
}


/**
 * Gestisce la generazione dei PDF per le richieste di trasporto.
 *
 * @since 1.0.0
 */
class PDFGenerator {

	/**
	 * Genera e salva un file PDF per una data richiesta.
	 *
	 * @param array $data Dati della richiesta.
	 * @param int $request_id ID della richiesta.
	 * @return string|false Percorso del file PDF generato o false in caso di errore.
	 */
	public function generate( array $data, int $request_id ): string|false {
		$pdf = $this->create_pdf_instance($data, $request_id);
		$upload_dir = wp_upload_dir();
		$pdf_path = $upload_dir['basedir'] . "/cri-requests/request-$request_id.pdf";

		if (!file_exists(dirname($pdf_path))) {
			wp_mkdir_p(dirname($pdf_path));
		}

		try {
			$pdf->Output($pdf_path, 'F');
			return $pdf_path;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Genera un PDF e lo invia direttamente al browser.
	 *
	 * @param array $data Dati della richiesta.
	 * @param int $request_id ID della richiesta.
	 */
	public function stream( array $data, int $request_id ): never {
		$pdf = $this->create_pdf_instance($data, $request_id);
		$pdf->Output("richiesta-{$request_id}.pdf", 'I');
		die();
	}

	/**
	 * Crea e configura l'istanza del PDF con i contenuti.
	 *
	 * @param array $data
	 * @param int $request_id
	 * @return CriveTCPDF
	 */
	private function create_pdf_instance(array $data, int $request_id): CriveTCPDF
	{
		$pdf = new CriveTCPDF('P', 'px', 'A4', true, 'UTF-8', false);

		$pdf->SetCreator('CRI Venezia Trasporti');
		$pdf->SetAuthor('Croce Rossa Italiana - Comitato di Venezia');
		$pdf->SetTitle("Richiesta di Trasporto N. $request_id");
		$pdf->SetSubject('Riepilogo Richiesta di Trasporto Sanitario');

		$pdf->setPrintHeader(true);
		$pdf->setPrintFooter(true);

		$pdf->SetMargins(42, 127, 42); // Margini: Left, Top, Right
		$pdf->SetHeaderMargin(14);
		$pdf->SetFooterMargin(28);

		$pdf->SetAutoPageBreak(true, 71); // Distanza dal fondo in pixel

		$pdf->AddPage();
		$pdf->SetFont('helvetica', '', 11);
		$pdf->SetFillColor(245, 245, 245);

		// Titolo del documento
		$pdf->SetFont('', 'B', 12);
		$pdf->Cell(0, 28, "Riepilogo Richiesta N. $request_id del " . wp_date( get_option('date_format') ), 0, 1, 'L');
		$pdf->Ln(14);

		// Costruisce e renderizza le sezioni del contenuto
		$content = $this->build_content_structure($data);
		foreach ($content as $section_title => $section_details) {
			$this->render_section($pdf, $section_title, $section_details);
		}

		return $pdf;
	}

	/**
	 * Costruisce una struttura dati organizzata per il contenuto del PDF.
	 *
	 * @param array $data Dati sanificati dal form.
	 * @return array Struttura del contenuto.
	 */
	private function build_content_structure(array $data): array
	{
		$structure = [];

		// Sezione 1: Riepilogo
		$structure['Riepilogo Richiesta'] = [
			'Nome Richiedente' => $data['nome_cognome'] ?? '',
			'Data Trasporto'   => $data['data_trasporto'] ?? '',
			'Email Richiedente' => $data['recapito_email'] ?? '',
			'Telefono Richiedente' => $data['recapito_telefonico'] ?? '',
		];

		// Sezione 2: Dettagli Trasporto
		$transport_details = [];
		$motivo_value = $data['motivo_trasporto'] ?? '';
		if ($motivo_value) {
			$transport_details['Motivo del Trasporto'] = $this->get_motivo_label($motivo_value);

			match ($motivo_value) {
				'visita' => $transport_details += [
					'Tipologia Visita' => $data['tipologia_visita'] ?? '',
					'Orario Visita' => $data['orario_visita'] ?? '',
					'Durata Stimata (min)' => $data['tempo_visita'] ?? '',
				],
				'trasferimento' => $transport_details += [
					'Da Struttura' => $data['struttura_da'] ?? '',
					'A Struttura' => $data['struttura_a'] ?? '',
				],
				'ricovero' => $transport_details += [
					'Orario Ricovero' => $data['orario_ricovero'] ?? '',
				],
				'dimissioni' => $transport_details += [
					'Orario Dimissioni' => $data['orario_dimissioni'] ?? '',
				],
				default => null,
			};
		}
		$structure['Dettagli Trasporto'] = $transport_details;

		// Sezione 3: Luogo Intervento
		$location_details = [];
		$luogo_value = $data['luogo_intervento'] ?? '';
		if ($luogo_value) {
			$location_details['Tipo Luogo'] = $this->get_luogo_label($luogo_value);
			$location_details['Indirizzo'] = $data['indirizzo_intervento'] ?? '';

			match ($luogo_value) {
				'ospedale', 'rsa', 'ambulatorio' => $location_details += [
					'Nome Struttura' => $data['nome_struttura'] ?? ''
				],
				'domicilio' => $location_details += [
					                                    'Piano' => $data['piano'] ?? '',
					                                    'Ascensore' => isset($data['ascensore']) && $data['ascensore'] ? 'Sì' : 'No',
				                                    ] + (!($data['ascensore'] ?? true) ? ['Larghezza Scale' => $data['larghezza_scale'] ?? ''] : []),
				default => null,
			};
		}
		$structure['Luogo Intervento'] = $location_details;

		// Sezione 4: Dati Paziente
		$structure['Dati Paziente'] = [
			'Data di Nascita' => $data['data_nascita'] ?? '',
			'Luogo di Nascita' => $data['luogo_nascita'] ?? '',
			'Codice Fiscale' => $data['codice_fiscale'] ?? '',
		];

		return $structure;
	}

	/**
	 * Renderizza una singola sezione del PDF.
	 */
	private function render_section(TCPDF $pdf, string $title, array $details): void {
		if (empty(array_filter($details))) return;

		$pdf->SetFont('', 'B', 12);
		$pdf->Cell(0, 28, $title, 0, 1, 'L');
		$pdf->SetFont('', '', 11);

		foreach ($details as $label => $value) {
			if (isset($value) && $value !== '') {
				$pdf->Cell(170, 20, $label . ':', 0, 0, 'L', true);
				$pdf->MultiCell(0, 20, $value, 0, 'L', false, 1);
			}
		}
		$pdf->Ln(23);
	}

	private function get_motivo_label(string $value): string {
		return match($value) {
			'visita' => 'Visita',
			'trasferimento' => 'Trasferimento fra Strutture',
			'ricovero' => 'Ricovero',
			'dimissioni' => 'Dimissioni',
			'altro' => 'Altro',
			default => ucfirst($value),
		};
	}

	private function get_luogo_label(string $value): string {
		return match($value) {
			'domicilio' => 'Domicilio',
			'rsa' => 'RSA',
			'ambulatorio' => 'Ambulatorio',
			'ospedale' => 'Ospedale',
			default => ucfirst($value),
		};
	}
}

