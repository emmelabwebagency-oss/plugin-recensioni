<?php
/**
 * Plugin Name: Review Stars Redirect
 * Plugin URI:  https://github.com/emmelabwebagency-oss/plugin-recensioni
 * Description: Mostra 5 stelline cliccabili tramite shortcode. In base alla valutazione, reindirizza l'utente a link diversi configurabili dal backend. Compatibile con Elementor.
 * Version:     1.0.0
 * Author:      Emmelab Web Agency
 * Author URI:  https://emmelabwebagency.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: review-stars-redirect
 * Domain Path: /languages
 */

// Impedisci l'accesso diretto al file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Costanti del plugin.
 */
define( 'RSR_VERSION', '1.0.0' );
define( 'RSR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RSR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'RSR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * ============================================================
 * SEZIONE ADMIN: Pagina impostazioni nel pannello WordPress
 * ============================================================
 */

/**
 * Registra la pagina di impostazioni nel menu admin.
 */
function rsr_add_admin_menu() {
	add_options_page(
		__( 'Review Stars Redirect', 'review-stars-redirect' ),
		__( 'Review Stars Redirect', 'review-stars-redirect' ),
		'manage_options',
		'review-stars-redirect',
		'rsr_settings_page_html'
	);
}
add_action( 'admin_menu', 'rsr_add_admin_menu' );

/**
 * Registra le impostazioni con la Settings API di WordPress.
 */
function rsr_register_settings() {
	// Registra il gruppo di opzioni.
	register_setting(
		'rsr_settings_group',
		'rsr_low_rating_url',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'default'           => '',
		)
	);

	register_setting(
		'rsr_settings_group',
		'rsr_high_rating_url',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'default'           => '',
		)
	);

	// Sezione impostazioni.
	add_settings_section(
		'rsr_main_section',
		__( 'Configurazione Redirect', 'review-stars-redirect' ),
		'rsr_section_description',
		'review-stars-redirect'
	);

	// Campo: link per valutazioni basse (1-3 stelle).
	add_settings_field(
		'rsr_low_rating_url',
		__( 'Link redirect (1-3 stelle)', 'review-stars-redirect' ),
		'rsr_low_rating_url_field',
		'review-stars-redirect',
		'rsr_main_section'
	);

	// Campo: link per valutazioni alte (4-5 stelle).
	add_settings_field(
		'rsr_high_rating_url',
		__( 'Link redirect (4-5 stelle)', 'review-stars-redirect' ),
		'rsr_high_rating_url_field',
		'review-stars-redirect',
		'rsr_main_section'
	);

	// Campo: shortcode in sola lettura.
	add_settings_field(
		'rsr_shortcode_display',
		__( 'Shortcode da copiare', 'review-stars-redirect' ),
		'rsr_shortcode_display_field',
		'review-stars-redirect',
		'rsr_main_section'
	);
}
add_action( 'admin_init', 'rsr_register_settings' );

/**
 * Descrizione della sezione impostazioni.
 */
function rsr_section_description() {
	echo '<p>' . esc_html__( 'Configura i link di redirect in base alla valutazione selezionata dall\'utente.', 'review-stars-redirect' ) . '</p>';
}

/**
 * Campo: URL per valutazioni basse (1-3 stelle).
 */
function rsr_low_rating_url_field() {
	$value = get_option( 'rsr_low_rating_url', '' );
	echo '<input type="url" name="rsr_low_rating_url" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="https://esempio.com/form-recensione" />';
	echo '<p class="description">' . esc_html__( 'L\'utente verrà reindirizzato a questo link se seleziona da 1 a 3 stelle (es. form recensione interna).', 'review-stars-redirect' ) . '</p>';
}

/**
 * Campo: URL per valutazioni alte (4-5 stelle).
 */
function rsr_high_rating_url_field() {
	$value = get_option( 'rsr_high_rating_url', '' );
	echo '<input type="url" name="rsr_high_rating_url" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="https://g.page/r/XXXX/review" />';
	echo '<p class="description">' . esc_html__( 'L\'utente verrà reindirizzato a questo link se seleziona 4 o 5 stelle (es. pagina recensioni Google).', 'review-stars-redirect' ) . '</p>';
}

/**
 * Campo: shortcode in sola lettura da copiare.
 */
function rsr_shortcode_display_field() {
	echo '<input type="text" value="[review_stars_redirect]" class="regular-text" readonly="readonly" onclick="this.select();" />';
	echo '<p class="description">' . esc_html__( 'Copia e incolla questo shortcode in qualsiasi pagina, post o widget Elementor.', 'review-stars-redirect' ) . '</p>';
}

/**
 * Renderizza la pagina delle impostazioni admin.
 */
function rsr_settings_page_html() {
	// Verifica permessi.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Messaggio di conferma salvataggio.
	if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		add_settings_error(
			'rsr_messages',
			'rsr_message',
			__( 'Impostazioni salvate con successo.', 'review-stars-redirect' ),
			'updated'
		);
	}
	settings_errors( 'rsr_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'rsr_settings_group' );
			do_settings_sections( 'review-stars-redirect' );
			submit_button( __( 'Salva Impostazioni', 'review-stars-redirect' ) );
			?>
		</form>
	</div>
	<?php
}

/**
 * Aggiunge il link "Impostazioni" nella pagina dei plugin.
 *
 * @param array $links Array dei link esistenti.
 * @return array Array dei link aggiornato.
 */
function rsr_plugin_action_links( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=review-stars-redirect' ) ) . '">' . esc_html__( 'Impostazioni', 'review-stars-redirect' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . RSR_PLUGIN_BASENAME, 'rsr_plugin_action_links' );

/**
 * Carica il foglio di stile admin nella pagina impostazioni del plugin.
 *
 * @param string $hook Hook della pagina corrente.
 */
function rsr_admin_enqueue_scripts( $hook ) {
	if ( 'settings_page_review-stars-redirect' !== $hook ) {
		return;
	}
	wp_enqueue_style(
		'rsr-admin-style',
		RSR_PLUGIN_URL . 'css/admin-style.css',
		array(),
		RSR_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'rsr_admin_enqueue_scripts' );

/**
 * ============================================================
 * SEZIONE FRONTEND: Shortcode e asset
 * ============================================================
 */

/**
 * Registra e renderizza lo shortcode [review_stars_redirect].
 *
 * @param array $atts Attributi dello shortcode (non utilizzati).
 * @return string HTML dello shortcode.
 */
function rsr_render_shortcode( $atts ) {
	// Recupera i link configurati.
	$low_url  = get_option( 'rsr_low_rating_url', '' );
	$high_url = get_option( 'rsr_high_rating_url', '' );

	// Carica CSS e JS solo quando lo shortcode è usato.
	wp_enqueue_style(
		'rsr-frontend-style',
		RSR_PLUGIN_URL . 'css/review-stars-redirect.css',
		array(),
		RSR_VERSION
	);

	wp_enqueue_script(
		'rsr-frontend-script',
		RSR_PLUGIN_URL . 'js/review-stars-redirect.js',
		array(),
		RSR_VERSION,
		true
	);

	// Passa le variabili al JavaScript frontend.
	wp_localize_script(
		'rsr-frontend-script',
		'rsrData',
		array(
			'lowUrl'  => esc_url( $low_url ),
			'highUrl' => esc_url( $high_url ),
		)
	);

	// Genera l'HTML delle stelline.
	$output  = '<div class="rsr-stars-wrapper" role="group" aria-label="' . esc_attr__( 'Lascia la tua recensione', 'review-stars-redirect' ) . '">';
	$output .= '<p class="rsr-tooltip">' . esc_html__( 'Lascia la tua recensione', 'review-stars-redirect' ) . '</p>';
	$output .= '<div class="rsr-stars">';

	for ( $i = 1; $i <= 5; $i++ ) {
		$output .= '<span class="rsr-star" data-value="' . esc_attr( $i ) . '" role="button" tabindex="0" aria-label="' . esc_attr(
			/* translators: %d: numero della stella */
			sprintf( __( '%d stella', 'review-stars-redirect' ), $i )
		) . '">';
		// SVG stella — leggero, scalabile, senza dipendenze esterne.
		$output .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" class="rsr-star-svg">';
		$output .= '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01L12 2z"/>';
		$output .= '</svg>';
		$output .= '</span>';
	}

	$output .= '</div>'; // .rsr-stars
	$output .= '</div>'; // .rsr-stars-wrapper

	return $output;
}
add_shortcode( 'review_stars_redirect', 'rsr_render_shortcode' );

/**
 * Imposta i valori predefiniti al momento dell'attivazione del plugin.
 */
function rsr_activate() {
	if ( false === get_option( 'rsr_low_rating_url' ) ) {
		add_option( 'rsr_low_rating_url', '' );
	}
	if ( false === get_option( 'rsr_high_rating_url' ) ) {
		add_option( 'rsr_high_rating_url', '' );
	}
}
register_activation_hook( __FILE__, 'rsr_activate' );

/**
 * Pulizia delle opzioni alla disinstallazione del plugin.
 */
function rsr_uninstall() {
	delete_option( 'rsr_low_rating_url' );
	delete_option( 'rsr_high_rating_url' );
}
register_uninstall_hook( __FILE__, 'rsr_uninstall' );
