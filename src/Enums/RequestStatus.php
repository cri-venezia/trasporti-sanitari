<?php

namespace CriVenice\Transport\Enums;

/**
 * Enum per gli stati delle richieste di trasporto.
 *
 * @since 1.0.0
 */
enum RequestStatus: string {
	case Pending = 'pending';
	case Confirmed = 'confirmed';

	/**
	 * Restituisce una etichetta leggibile per lo stato.
	 *
	 * @return string
	 */
	public function label(): string {
		return match ($this) {
			self::Pending => esc_html__('In Attesa', 'cri-trasporti'),
			self::Confirmed => esc_html__('Confermato', 'cri-trasporti'),
		};
	}
}
