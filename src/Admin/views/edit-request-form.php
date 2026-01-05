<?php
/**
 * Vista per il form di modifica richiesta.
 *
 * @var object $request L'oggetto richiesta recuperato dal DB.
 * @var array $details I dettagli decodificati dal JSON.
 */
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form method="POST" action="<?php echo esc_url( admin_url( 'admin.php?page=crive-transport-requests' ) ); ?>" class="crive-admin-form">
		<?php wp_nonce_field( 'crive_edit_request', 'crive_edit_request_nonce' ); ?>
		<input type="hidden" name="request_id" value="<?php echo esc_attr($request->id); ?>">
		<input type="hidden" name="action" value="update_request">

		<h2><?php esc_html_e( 'Dati Richiedente e Trasporto', 'cri-trasporti' ); ?></h2>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="nome_cognome"><?php esc_html_e( 'Nome e Cognome', 'cri-trasporti' ); ?></label></th>
				<td><input type="text" name="nome_cognome" id="nome_cognome" class="regular-text" required value="<?php echo esc_attr($request->nome_cognome); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="data_trasporto"><?php esc_html_e( 'Data del Trasporto', 'cri-trasporti' ); ?></label></th>
				<td><input type="date" name="data_trasporto" id="data_trasporto" required value="<?php echo esc_attr($request->data_trasporto); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="recapito_telefonico"><?php esc_html_e( 'Recapito Telefonico', 'cri-trasporti' ); ?></label></th>
				<td><input type="tel" name="recapito_telefonico" id="recapito_telefonico" class="regular-text" required value="<?php echo esc_attr($request->recapito_telefonico); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="recapito_email"><?php esc_html_e( 'Recapito Email', 'cri-trasporti' ); ?></label></th>
				<td><input type="email" name="recapito_email" id="recapito_email" class="regular-text" required value="<?php echo esc_attr($request->recapito_email); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="motivo_trasporto"><?php esc_html_e( 'Motivo del Trasporto', 'cri-trasporti' ); ?></label></th>
				<td>
					<select name="motivo_trasporto" id="motivo_trasporto">
						<option value="visita" <?php selected($request->motivo_trasporto, 'visita'); ?>><?php esc_html_e( 'Visita', 'cri-trasporti' ); ?></option>
						<option value="trasferimento" <?php selected($request->motivo_trasporto, 'trasferimento'); ?>><?php esc_html_e( 'Trasferimento fra Strutture', 'cri-trasporti' ); ?></option>
						<option value="ricovero" <?php selected($request->motivo_trasporto, 'ricovero'); ?>><?php esc_html_e( 'Ricovero', 'cri-trasporti' ); ?></option>
						<option value="dimissioni" <?php selected($request->motivo_trasporto, 'dimissioni'); ?>><?php esc_html_e( 'Dimissioni', 'cri-trasporti' ); ?></option>
						<option value="altro" <?php selected($request->motivo_trasporto, 'altro'); ?>><?php esc_html_e( 'Altro', 'cri-trasporti' ); ?></option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Luogo e Destinazione', 'cri-trasporti' ); ?></h2>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="luogo_intervento"><?php esc_html_e( 'Tipo Luogo Ritiro', 'cri-trasporti' ); ?></label></th>
				<td>
					<select name="luogo_intervento" id="luogo_intervento">
						<option value="domicilio" <?php selected($request->luogo_intervento, 'domicilio'); ?>><?php esc_html_e( 'Domicilio', 'cri-trasporti' ); ?></option>
						<option value="ambulatorio" <?php selected($request->luogo_intervento, 'ambulatorio'); ?>><?php esc_html_e( 'Ambulatorio', 'cri-trasporti' ); ?></option>
						<option value="ospedale" <?php selected($request->luogo_intervento, 'ospedale'); ?>><?php esc_html_e( 'Ospedale', 'cri-trasporti' ); ?></option>
						<option value="rsa" <?php selected($request->luogo_intervento, 'rsa'); ?>><?php esc_html_e( 'RSA', 'cri-trasporti' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="indirizzo_intervento"><?php esc_html_e( 'Indirizzo Ritiro', 'cri-trasporti' ); ?></label></th>
				<td><textarea name="indirizzo_intervento" id="indirizzo_intervento" class="large-text" rows="3"><?php echo esc_textarea($request->indirizzo_intervento); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="indirizzo_destinazione"><?php esc_html_e( 'Indirizzo Destinazione', 'cri-trasporti' ); ?></label></th>
				<td><textarea name="indirizzo_destinazione" id="indirizzo_destinazione" class="large-text" rows="3"><?php echo esc_textarea($details['indirizzo_destinazione'] ?? ''); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="piano"><?php esc_html_e( 'Piano (se domicilio)', 'cri-trasporti' ); ?></label></th>
				<td><input type="text" name="piano" id="piano" value="<?php echo esc_attr($request->piano); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="ascensore"><?php esc_html_e( 'Ascensore', 'cri-trasporti' ); ?></label></th>
				<td>
					<select name="ascensore" id="ascensore">
						<option value="presente" <?php selected($request->ascensore, '1'); ?>><?php esc_html_e( 'Presente', 'cri-trasporti' ); ?></option>
						<option value="assente" <?php selected($request->ascensore, '0'); ?>><?php esc_html_e( 'Assente', 'cri-trasporti' ); ?></option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Dati Paziente', 'cri-trasporti' ); ?></h2>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="data_nascita"><?php esc_html_e( 'Data di Nascita', 'cri-trasporti' ); ?></label></th>
				<td><input type="date" name="data_nascita" id="data_nascita" value="<?php echo esc_attr($details['data_nascita'] ?? ''); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="luogo_nascita"><?php esc_html_e( 'Luogo di Nascita', 'cri-trasporti' ); ?></label></th>
				<td><input type="text" name="luogo_nascita" id="luogo_nascita" class="regular-text" value="<?php echo esc_attr($details['luogo_nascita'] ?? ''); ?>"></td>
			</tr>
			<tr>
				<th scope="row"><label for="codice_fiscale"><?php esc_html_e( 'Codice Fiscale', 'cri-trasporti' ); ?></label></th>
				<td><input type="text" name="codice_fiscale" id="codice_fiscale" class="regular-text" value="<?php echo esc_attr($request->codice_fiscale); ?>"></td>
			</tr>
			</tbody>
		</table>

		<?php submit_button( esc_html__( 'Aggiorna Richiesta', 'cri-trasporti' ) ); ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=crive-transport-requests' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Annulla', 'cri-trasporti' ); ?></a>
	</form>
</div>
