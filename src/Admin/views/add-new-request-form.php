<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form method="POST" action="<?php echo esc_url( admin_url( 'admin.php?page=crive-transport-requests' ) ); ?>" class="crive-admin-form">
		<?php wp_nonce_field( 'crive_add_new_request', 'crive_add_new_request_nonce' ); ?>

		<h2><?php esc_html_e( 'Dati Richiedente e Trasporto', 'cri-trasporti' ); ?></h2>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="nome_cognome"><?php esc_html_e( 'Nome e Cognome', 'cri-trasporti' ); ?></label></th>
				<td><input type="text" name="nome_cognome" id="nome_cognome" class="regular-text" required></td>
			</tr>
			<tr>
				<th scope="row"><label for="data_trasporto"><?php esc_html_e( 'Data del Trasporto', 'cri-trasporti' ); ?></label></th>
				<td><input type="date" name="data_trasporto" id="data_trasporto" required></td>
			</tr>
			<tr>
				<th scope="row"><label for="recapito_telefonico"><?php esc_html_e( 'Recapito Telefonico', 'cri-trasporti' ); ?></label></th>
				<td><input type="tel" name="recapito_telefonico" id="recapito_telefonico" class="regular-text" required></td>
			</tr>
			<tr>
				<th scope="row"><label for="recapito_email"><?php esc_html_e( 'Recapito Email', 'cri-trasporti' ); ?></label></th>
				<td><input type="email" name="recapito_email" id="recapito_email" class="regular-text" required></td>
			</tr>
			<tr>
				<th scope="row"><label for="motivo_trasporto"><?php esc_html_e( 'Motivo del Trasporto', 'cri-trasporti' ); ?></label></th>
				<td>
					<select name="motivo_trasporto" id="motivo_trasporto">
						<option value="Visita"><?php esc_html_e( 'Visita', 'cri-trasporti' ); ?></option>
						<option value="Trasferimento fra Strutture"><?php esc_html_e( 'Trasferimento fra Strutture', 'cri-trasporti' ); ?></option>
						<option value="Ricovero"><?php esc_html_e( 'Ricovero', 'cri-trasporti' ); ?></option>
						<option value="Dimissioni"><?php esc_html_e( 'Dimissioni', 'cri-trasporti' ); ?></option>
						<option value="Altro"><?php esc_html_e( 'Altro', 'cri-trasporti' ); ?></option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>

		<!-- Qui andrebbero inseriti i campi condizionali con un po' di JavaScript -->
		<!-- Per semplicità in questa versione, li omettiamo ma sarebbero da aggiungere -->

		<h2><?php esc_html_e( 'Dati Paziente', 'cri-trasporti' ); ?></h2>
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="data_nascita"><?php esc_html_e( 'Data di Nascita', 'cri-trasporti' ); ?></label></th>
				<td><input type="date" name="data_nascita" id="data_nascita"></td>
			</tr>
			<tr>
				<th scope="row"><label for="luogo_nascita"><?php esc_html_e( 'Luogo di Nascita', 'cri-trasporti' ); ?></label></th>
				<td><input type="text" name="luogo_nascita" id="luogo_nascita" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="codice_fiscale"><?php esc_html_e( 'Codice Fiscale', 'cri-trasporti' ); ?></label></th>
				<td><input type="text" name="codice_fiscale" id="codice_fiscale" class="regular-text"></td>
			</tr>
			</tbody>
		</table>

		<?php submit_button( esc_html__( 'Crea Richiesta', 'cri-trasporti' ) ); ?>
	</form>
</div>
