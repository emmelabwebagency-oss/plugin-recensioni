<?php
/**
 * Plugin Name: Review Stars Redirect
 * Plugin URI:  https://github.com/emmelabwebagency-oss/plugin-recensioni
 * Description: Crea multipli shortcode con 5 stelline cliccabili, ognuno con regole di redirect personalizzabili per ogni stella. Compatibile con Elementor.
 * Version:     2.0.0
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
define( 'RSR_VERSION', '2.0.0' );
define( 'RSR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RSR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'RSR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * ============================================================
 * HELPER: Gestione dati istanze
 * ============================================================
 */

/**
 * Recupera tutte le istanze salvate.
 *
 * @return array Array di istanze. Ogni istanza ha: id, name, rules.
 *               Ogni regola ha: stars (array di int), url (string).
 */
function rsr_get_instances() {
	$instances = get_option( 'rsr_instances', array() );
	if ( ! is_array( $instances ) ) {
		return array();
	}
	return $instances;
}

/**
 * Salva le istanze.
 *
 * @param array $instances Array di istanze.
 */
function rsr_save_instances( $instances ) {
	update_option( 'rsr_instances', $instances );
}

/**
 * Recupera una singola istanza per ID.
 *
 * @param string $id ID dell'istanza.
 * @return array|null L'istanza o null se non trovata.
 */
function rsr_get_instance( $id ) {
	$instances = rsr_get_instances();
	foreach ( $instances as $instance ) {
		if ( isset( $instance['id'] ) && $instance['id'] === $id ) {
			return $instance;
		}
	}
	return null;
}

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
 * Gestisce le azioni admin (salva, elimina, crea istanze).
 */
function rsr_handle_admin_actions() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Creazione nuova istanza.
	if ( isset( $_POST['rsr_action'] ) && 'create' === $_POST['rsr_action'] ) {
		check_admin_referer( 'rsr_create_instance' );

		$name = isset( $_POST['rsr_new_name'] ) ? sanitize_text_field( wp_unslash( $_POST['rsr_new_name'] ) ) : '';
		if ( empty( $name ) ) {
			$name = __( 'Nuovo Shortcode', 'review-stars-redirect' );
		}

		$instances   = rsr_get_instances();
		$new_id      = 'rsr_' . wp_generate_password( 8, false, false );
		$instances[] = array(
			'id'    => $new_id,
			'name'  => $name,
			'rules' => array(
				array(
					'stars' => array( 1, 2, 3 ),
					'url'   => '',
				),
				array(
					'stars' => array( 4, 5 ),
					'url'   => '',
				),
			),
		);
		rsr_save_instances( $instances );

		wp_safe_redirect( add_query_arg(
			array(
				'page'       => 'review-stars-redirect',
				'edit'       => $new_id,
				'rsr_notice' => 'created',
			),
			admin_url( 'options-general.php' )
		) );
		exit;
	}

	// Salvataggio istanza.
	if ( isset( $_POST['rsr_action'] ) && 'save' === $_POST['rsr_action'] ) {
		$edit_id = isset( $_POST['rsr_instance_id'] ) ? sanitize_text_field( wp_unslash( $_POST['rsr_instance_id'] ) ) : '';
		check_admin_referer( 'rsr_save_instance_' . $edit_id );

		$instances = rsr_get_instances();
		$name      = isset( $_POST['rsr_instance_name'] ) ? sanitize_text_field( wp_unslash( $_POST['rsr_instance_name'] ) ) : '';

		// Ricostruisci le regole dal POST.
		$rules      = array();
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$rule_stars = isset( $_POST['rsr_rule_stars'] ) && is_array( $_POST['rsr_rule_stars'] ) ? $_POST['rsr_rule_stars'] : array();
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$rule_urls  = isset( $_POST['rsr_rule_urls'] ) && is_array( $_POST['rsr_rule_urls'] ) ? $_POST['rsr_rule_urls'] : array();

		// Unisci le chiavi di entrambi gli array per gestire indici non contigui
		// (es. quando l'utente rimuove una regola intermedia nell'admin JS).
		$all_keys = array_unique( array_merge( array_keys( $rule_stars ), array_keys( $rule_urls ) ) );
		foreach ( $all_keys as $i ) {
			$stars_raw = isset( $rule_stars[ $i ] ) && is_array( $rule_stars[ $i ] ) ? $rule_stars[ $i ] : array();
			$url_raw   = isset( $rule_urls[ $i ] ) ? $rule_urls[ $i ] : '';

			// Sanitizza le stelle (solo valori 1-5).
			$stars = array();
			foreach ( $stars_raw as $s ) {
				$s_int = absint( $s );
				if ( $s_int >= 1 && $s_int <= 5 ) {
					$stars[] = $s_int;
				}
			}

			$url = esc_url_raw( wp_unslash( $url_raw ) );

			// Salva solo regole con almeno una stella selezionata.
			if ( ! empty( $stars ) ) {
				$rules[] = array(
					'stars' => $stars,
					'url'   => $url,
				);
			}
		}

		// Aggiorna l'istanza.
		foreach ( $instances as &$inst ) {
			if ( $inst['id'] === $edit_id ) {
				$inst['name']  = $name;
				$inst['rules'] = $rules;
				break;
			}
		}
		unset( $inst );

		rsr_save_instances( $instances );

		wp_safe_redirect( add_query_arg(
			array(
				'page'       => 'review-stars-redirect',
				'edit'       => $edit_id,
				'rsr_notice' => 'saved',
			),
			admin_url( 'options-general.php' )
		) );
		exit;
	}

	// Eliminazione istanza.
	if ( isset( $_GET['rsr_delete'] ) && isset( $_GET['_wpnonce'] ) ) {
		$delete_id = sanitize_text_field( wp_unslash( $_GET['rsr_delete'] ) );
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'rsr_delete_' . $delete_id ) ) {
			wp_die( esc_html__( 'Nonce non valido.', 'review-stars-redirect' ) );
		}

		$instances = rsr_get_instances();
		$instances = array_filter( $instances, function ( $inst ) use ( $delete_id ) {
			return $inst['id'] !== $delete_id;
		});
		$instances = array_values( $instances );
		rsr_save_instances( $instances );

		wp_safe_redirect( add_query_arg(
			array(
				'page'       => 'review-stars-redirect',
				'rsr_notice' => 'deleted',
			),
			admin_url( 'options-general.php' )
		) );
		exit;
	}
}
add_action( 'admin_init', 'rsr_handle_admin_actions' );

/**
 * Renderizza la pagina delle impostazioni admin.
 */
function rsr_settings_page_html() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$instances = rsr_get_instances();

	// Mostra avvisi.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$notice = isset( $_GET['rsr_notice'] ) ? sanitize_text_field( wp_unslash( $_GET['rsr_notice'] ) ) : '';
	if ( 'saved' === $notice ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Impostazioni salvate con successo.', 'review-stars-redirect' ) . '</p></div>';
	} elseif ( 'created' === $notice ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Nuovo shortcode creato.', 'review-stars-redirect' ) . '</p></div>';
	} elseif ( 'deleted' === $notice ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Shortcode eliminato.', 'review-stars-redirect' ) . '</p></div>';
	}

	// Determina se stiamo modificando un'istanza.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$edit_id       = isset( $_GET['edit'] ) ? sanitize_text_field( wp_unslash( $_GET['edit'] ) ) : '';
	$edit_instance = null;
	if ( $edit_id ) {
		$edit_instance = rsr_get_instance( $edit_id );
	}

	?>
	<div class="wrap rsr-admin-wrap">
		<h1><?php esc_html_e( 'Review Stars Redirect', 'review-stars-redirect' ); ?></h1>
		<p class="rsr-admin-description">
			<?php esc_html_e( 'Crea e gestisci i tuoi shortcode con stelline. Ogni shortcode può avere regole di redirect personalizzate per ogni stella.', 'review-stars-redirect' ); ?>
		</p>

		<?php if ( $edit_instance ) : ?>
			<!-- MODIFICA ISTANZA -->
			<div class="rsr-edit-section">
				<h2>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: nome dell'istanza */
							__( 'Modifica: %s', 'review-stars-redirect' ),
							$edit_instance['name']
						)
					);
					?>
				</h2>
				<p>
					<strong><?php esc_html_e( 'Shortcode:', 'review-stars-redirect' ); ?></strong>
					<code class="rsr-shortcode-code" onclick="if(window.getSelection){var r=document.createRange();r.selectNodeContents(this);var s=window.getSelection();s.removeAllRanges();s.addRange(r);document.execCommand('copy');}">
						[review_stars_redirect id="<?php echo esc_attr( $edit_instance['id'] ); ?>"]
					</code>
					<span class="rsr-copy-hint"><?php esc_html_e( '(clicca per copiare)', 'review-stars-redirect' ); ?></span>
				</p>

				<form method="post" action="">
					<?php wp_nonce_field( 'rsr_save_instance_' . $edit_instance['id'] ); ?>
					<input type="hidden" name="rsr_action" value="save" />
					<input type="hidden" name="rsr_instance_id" value="<?php echo esc_attr( $edit_instance['id'] ); ?>" />

					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="rsr_instance_name"><?php esc_html_e( 'Nome', 'review-stars-redirect' ); ?></label>
							</th>
							<td>
								<input type="text" id="rsr_instance_name" name="rsr_instance_name"
									value="<?php echo esc_attr( $edit_instance['name'] ); ?>"
									class="regular-text" />
								<p class="description"><?php esc_html_e( 'Un nome identificativo per riconoscere questo shortcode (uso interno).', 'review-stars-redirect' ); ?></p>
							</td>
						</tr>
					</table>

					<h3><?php esc_html_e( 'Regole di Redirect', 'review-stars-redirect' ); ?></h3>
					<p class="description">
						<?php esc_html_e( 'Per ogni regola, seleziona le stelle e inserisci l\'URL di redirect. Puoi aggiungere quante regole vuoi.', 'review-stars-redirect' ); ?>
					</p>

					<div id="rsr-rules-container">
						<?php
						$rules = isset( $edit_instance['rules'] ) ? $edit_instance['rules'] : array();
						if ( empty( $rules ) ) {
							$rules = array(
								array(
									'stars' => array(),
									'url'   => '',
								),
							);
						}
						$rule_index = 0;
						foreach ( $rules as $rule ) :
							?>
							<div class="rsr-rule-row" data-index="<?php echo esc_attr( $rule_index ); ?>">
								<div class="rsr-rule-header">
									<strong><?php echo esc_html( sprintf( __( 'Regola %d', 'review-stars-redirect' ), $rule_index + 1 ) ); ?></strong>
									<button type="button" class="button rsr-remove-rule" title="<?php esc_attr_e( 'Rimuovi regola', 'review-stars-redirect' ); ?>">&times;</button>
								</div>
								<div class="rsr-rule-content">
									<div class="rsr-rule-stars">
										<label><?php esc_html_e( 'Stelle:', 'review-stars-redirect' ); ?></label>
										<div class="rsr-star-checkboxes">
											<?php for ( $s = 1; $s <= 5; $s++ ) : ?>
												<label class="rsr-star-checkbox">
													<input type="checkbox"
														name="rsr_rule_stars[<?php echo esc_attr( $rule_index ); ?>][]"
														value="<?php echo esc_attr( $s ); ?>"
														<?php checked( in_array( $s, $rule['stars'], true ) ); ?> />
													<?php echo esc_html( $s ); ?> &#9733;
												</label>
											<?php endfor; ?>
										</div>
									</div>
									<div class="rsr-rule-url">
										<label><?php esc_html_e( 'URL redirect:', 'review-stars-redirect' ); ?></label>
										<input type="url"
											name="rsr_rule_urls[<?php echo esc_attr( $rule_index ); ?>]"
											value="<?php echo esc_attr( $rule['url'] ); ?>"
											class="regular-text"
											placeholder="https://esempio.com/link" />
									</div>
								</div>
							</div>
							<?php
							$rule_index++;
						endforeach;
						?>
					</div>

					<p>
						<button type="button" id="rsr-add-rule" class="button button-secondary">
							+ <?php esc_html_e( 'Aggiungi Regola', 'review-stars-redirect' ); ?>
						</button>
					</p>

					<?php submit_button( __( 'Salva Impostazioni', 'review-stars-redirect' ) ); ?>
				</form>

				<p>
					<a href="<?php echo esc_url( admin_url( 'options-general.php?page=review-stars-redirect' ) ); ?>" class="button">
						&larr; <?php esc_html_e( 'Torna alla lista', 'review-stars-redirect' ); ?>
					</a>
				</p>
			</div>

		<?php else : ?>
			<!-- LISTA ISTANZE -->
			<div class="rsr-list-section">
				<?php if ( ! empty( $instances ) ) : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Nome', 'review-stars-redirect' ); ?></th>
								<th><?php esc_html_e( 'Shortcode', 'review-stars-redirect' ); ?></th>
								<th><?php esc_html_e( 'Regole', 'review-stars-redirect' ); ?></th>
								<th><?php esc_html_e( 'Azioni', 'review-stars-redirect' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $instances as $instance ) : ?>
								<tr>
									<td><strong><?php echo esc_html( $instance['name'] ); ?></strong></td>
									<td>
										<code class="rsr-shortcode-code" onclick="if(window.getSelection){var r=document.createRange();r.selectNodeContents(this);var s=window.getSelection();s.removeAllRanges();s.addRange(r);document.execCommand('copy');}">
											[review_stars_redirect id="<?php echo esc_attr( $instance['id'] ); ?>"]
										</code>
									</td>
									<td>
										<?php
										$rules_count = isset( $instance['rules'] ) ? count( $instance['rules'] ) : 0;
										echo esc_html(
											sprintf(
												/* translators: %d: numero di regole */
												_n( '%d regola', '%d regole', $rules_count, 'review-stars-redirect' ),
												$rules_count
											)
										);
										?>
									</td>
									<td>
										<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'review-stars-redirect', 'edit' => $instance['id'] ), admin_url( 'options-general.php' ) ) ); ?>" class="button button-small">
											<?php esc_html_e( 'Modifica', 'review-stars-redirect' ); ?>
										</a>
										<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'review-stars-redirect', 'rsr_delete' => $instance['id'] ), admin_url( 'options-general.php' ) ), 'rsr_delete_' . $instance['id'] ) ); ?>"
											class="button button-small rsr-delete-btn"
											onclick="return confirm('<?php echo esc_js( __( 'Sei sicuro di voler eliminare questo shortcode?', 'review-stars-redirect' ) ); ?>');">
											<?php esc_html_e( 'Elimina', 'review-stars-redirect' ); ?>
										</a>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p class="rsr-no-instances"><?php esc_html_e( 'Nessuno shortcode creato. Crea il primo qui sotto!', 'review-stars-redirect' ); ?></p>
				<?php endif; ?>

				<!-- FORM CREAZIONE -->
				<div class="rsr-create-section">
					<h2><?php esc_html_e( 'Crea Nuovo Shortcode', 'review-stars-redirect' ); ?></h2>
					<form method="post" action="">
						<?php wp_nonce_field( 'rsr_create_instance' ); ?>
						<input type="hidden" name="rsr_action" value="create" />
						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="rsr_new_name"><?php esc_html_e( 'Nome', 'review-stars-redirect' ); ?></label>
								</th>
								<td>
									<input type="text" id="rsr_new_name" name="rsr_new_name" class="regular-text"
										placeholder="<?php esc_attr_e( 'Es. Homepage, Pagina Contatti...', 'review-stars-redirect' ); ?>" />
									<p class="description"><?php esc_html_e( 'Un nome identificativo (uso interno, non visibile ai visitatori).', 'review-stars-redirect' ); ?></p>
								</td>
							</tr>
						</table>
						<?php submit_button( __( 'Crea Shortcode', 'review-stars-redirect' ), 'primary' ); ?>
					</form>
				</div>
			</div>
		<?php endif; ?>
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
 * Carica gli asset admin nella pagina impostazioni del plugin.
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
	wp_enqueue_script(
		'rsr-admin-script',
		RSR_PLUGIN_URL . 'js/admin-script.js',
		array(),
		RSR_VERSION,
		true
	);
	wp_localize_script(
		'rsr-admin-script',
		'rsrAdmin',
		array(
			'starLabel'      => __( 'Stelle:', 'review-stars-redirect' ),
			'urlLabel'       => __( 'URL redirect:', 'review-stars-redirect' ),
			'ruleLabel'      => __( 'Regola', 'review-stars-redirect' ),
			'removeTitle'    => __( 'Rimuovi regola', 'review-stars-redirect' ),
			'urlPlaceholder' => 'https://esempio.com/link',
		)
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
 * Ogni shortcode ha un attributo "id" che corrisponde all'istanza configurata.
 * Le regole di redirect sono passate come data attribute JSON.
 *
 * @param array $atts Attributi dello shortcode.
 * @return string HTML dello shortcode.
 */
function rsr_render_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'id' => '',
		),
		$atts,
		'review_stars_redirect'
	);

	$instance_id = sanitize_text_field( $atts['id'] );
	if ( empty( $instance_id ) ) {
		return '<!-- Review Stars Redirect: attributo id mancante -->';
	}

	$instance = rsr_get_instance( $instance_id );
	if ( ! $instance ) {
		return '<!-- Review Stars Redirect: istanza non trovata -->';
	}

	// Costruisci la mappa stella => URL dalle regole.
	$star_map = array();
	foreach ( $instance['rules'] as $rule ) {
		foreach ( $rule['stars'] as $star ) {
			$star_map[ (string) $star ] = $rule['url'];
		}
	}

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

	// Genera un ID univoco per il wrapper.
	static $instance_counter = 0;
	$instance_counter++;
	$wrapper_id = 'rsr-instance-' . $instance_counter;

	// Genera l'HTML delle stelline con la mappa dei redirect come data attribute.
	$output  = '<div class="rsr-stars-wrapper" id="' . esc_attr( $wrapper_id ) . '" role="group" aria-label="' . esc_attr__( 'Valutazione', 'review-stars-redirect' ) . '"';
	$output .= ' data-rsr-map="' . esc_attr( wp_json_encode( $star_map ) ) . '">';
	$output .= '<div class="rsr-stars">';

	for ( $i = 1; $i <= 5; $i++ ) {
		$output .= '<span class="rsr-star" data-value="' . esc_attr( $i ) . '" role="button" tabindex="0" aria-label="' . esc_attr(
			/* translators: %d: numero della stella */
			sprintf( __( '%d stella', 'review-stars-redirect' ), $i )
		) . '">';
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
	if ( false === get_option( 'rsr_instances' ) ) {
		add_option( 'rsr_instances', array() );
	}
}
register_activation_hook( __FILE__, 'rsr_activate' );

/**
 * Pulizia delle opzioni alla disinstallazione del plugin.
 */
function rsr_uninstall() {
	delete_option( 'rsr_instances' );
	// Pulizia vecchie opzioni v1.
	delete_option( 'rsr_low_rating_url' );
	delete_option( 'rsr_high_rating_url' );
	delete_option( 'rsr_rating_field_id' );
}
register_uninstall_hook( __FILE__, 'rsr_uninstall' );
