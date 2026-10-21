<?php
/** Site-local calendar editing and searchable content assignments. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Keeps calendar configuration within familiar WordPress editing screens. */
final class Admin {
	/** Register admin hooks, guarded saves and content search. @return void */
	public static function hooks(): void {
		add_action( 'add_meta_boxes', array( self::class, 'boxes' ) );
		add_action( 'save_post_' . Calendar::TYPE, array( self::class, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( self::class, 'assets' ) );
		add_action( 'wp_ajax_ttz_search', array( self::class, 'search' ) );
		add_action( 'in_admin_footer', array( self::class, 'footer' ) );
		add_action( 'admin_menu', array( self::class, 'menu' ), 20 );
		add_action( 'wp_ajax_ttz_create_door', array( self::class, 'create_door' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( FILE ), array( self::class, 'links' ) );
	}

	/** Add configuration and assignment boxes. @return void */
	public static function boxes(): void {
		add_meta_box( 'ttz-settings', __( 'Calendar settings', 'tuerchenzeit' ), array( self::class, 'settings_box' ), Calendar::TYPE, 'normal', 'high' );
		add_meta_box( 'ttz-doors', __( 'The 24 doors', 'tuerchenzeit' ), array( self::class, 'doors_box' ), Calendar::TYPE, 'normal', 'default' );
	}

	/** Load editing assets only on this plugin's screens.
	 * @return void
	 */
	public static function assets(): void {
		wp_enqueue_style( 'ttz-menu', plugins_url( 'assets/menu.css', FILE ), array(), VERSION );
		$screen = get_current_screen();
		if ( ! $screen || ( ! in_array( $screen->post_type, array( Calendar::TYPE, Calendar::DOOR ), true ) && ! str_contains( $screen->id, 'ttz-guide' ) ) ) { return; }
		if ( Calendar::TYPE === $screen->post_type ) { wp_enqueue_media(); }
		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( 'ttz-admin', plugins_url( 'assets/admin.css', FILE ), array(), VERSION );
		$colors = array();
		if ( 'edit' === $screen->base && Calendar::TYPE === $screen->post_type ) { foreach ( get_posts( array( 'post_type' => Calendar::TYPE, 'post_status' => array( 'publish', 'draft', 'pending', 'future', 'private' ), 'numberposts' => -1 ) ) as $calendar ) { $hex = sanitize_hex_color( Calendar::settings( $calendar->ID )['color'] ); if ( $hex ) { $colors[ $hex ] = '.ttz-color-' . substr( $hex, 1 ) . '{background-color:' . $hex . '}'; } } }
		if ( $colors ) { wp_add_inline_style( 'ttz-admin', implode( '', $colors ) ); }
		wp_enqueue_script( 'ttz-admin', plugins_url( 'assets/admin.js', FILE ), array( 'wp-element', 'wp-components' ), VERSION, true );
		wp_localize_script( 'ttz-admin', 'ttzAdmin', array( 'url' => admin_url( 'admin-ajax.php' ), 'checklistNonce' => wp_create_nonce( 'ttz_checklist' ), 'copied' => __( 'Copied.', 'tuerchenzeit' ), 'copyFallback' => __( 'Select and copy the shortcode manually.', 'tuerchenzeit' ), 'checklistError' => __( 'The checklist preference could not be saved. Please try again.', 'tuerchenzeit' ), 'allReady' => __( 'All doors are ready.', 'tuerchenzeit' ), 'nonce' => wp_create_nonce( 'ttz_search' ), 'empty' => __( 'No matching content found.', 'tuerchenzeit' ), 'error' => __( 'Search failed. Please try again.', 'tuerchenzeit' ), 'choose' => __( 'Choose content', 'tuerchenzeit' ), 'assign' => __( 'Assign content', 'tuerchenzeit' ), 'ready' => __( 'Ready', 'tuerchenzeit' ), 'draft' => __( 'Draft — publish this content', 'tuerchenzeit' ), 'none' => __( 'Not assigned', 'tuerchenzeit' ), 'removed' => __( 'Assignment removed. Save the calendar to keep this change.', 'tuerchenzeit' ), 'saved' => __( 'Content assigned. Save the calendar to keep this change.', 'tuerchenzeit' ), 'edit' => __( 'Edit assigned content', 'tuerchenzeit' ), 'reset' => __( 'Reset', 'tuerchenzeit' ), 'color' => __( 'Choose accent color', 'tuerchenzeit' ), 'dirty' => __( 'Unsaved changes.', 'tuerchenzeit' ), 'clean' => __( 'No unsaved changes.', 'tuerchenzeit' ), 'save' => __( 'Save changes', 'tuerchenzeit' ), 'publish' => __( 'Publish calendar', 'tuerchenzeit' ), 'full' => __( 'View full entry', 'tuerchenzeit' ), 'progress' => __( '%d of 24 doors ready', 'tuerchenzeit' ), 'heading' => __( 'Choose content for door %d', 'tuerchenzeit' ) ) );
	}

	/** Show calendar settings and ready-to-copy shortcodes.
	 * @param \WP_Post $post Calendar being edited.
	 * @return void
	 */
	public static function settings_box( \WP_Post $post ): void {
		$s = Calendar::settings( $post->ID );
		wp_nonce_field( 'ttz_save_' . $post->ID, 'ttz_nonce' );
		?>
		<div class="ttz-admin-masthead"><span>INSPIRATION · DECKERWEB</span><h2>Türchenzeit</h2><p><?php echo esc_html( Branding::text( 'slogan' ) ); ?></p></div>
		<section class="ttz-setting-group"><h3><?php esc_html_e( 'Year and appearance', 'tuerchenzeit' ); ?></h3><div class="ttz-fields">
		<label><?php esc_html_e( 'Calendar year', 'tuerchenzeit' ); ?><input name="ttz_settings[year]" type="number" min="2000" max="2199" required value="<?php echo esc_attr( $s['year'] ); ?>"></label>

		<div class="ttz-color-field"><label for="ttz-color"><?php esc_html_e( 'Accent color', 'tuerchenzeit' ); ?></label><input type="text" id="ttz-color" pattern="#[0-9a-fA-F]{6}" name="ttz_settings[color]" value="<?php echo esc_attr( $s['color'] ); ?>"><span id="ttz-color-root"></span></div>
		</div>
		<fieldset class="ttz-designs"><legend><?php esc_html_e( 'Design', 'tuerchenzeit' ); ?></legend>
		<?php foreach ( Polish::themes() as $key => $label ) : ?>
		<label class="ttz-design"><input type="radio" name="ttz_settings[theme]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $s['theme'] ?? 'evergreen', $key ); ?>><span class="ttz-swatch ttz-swatch-<?php echo esc_attr( $key ); ?>" aria-hidden="true"><span>✦</span><span class="ttz-mini-doors">1　2　3　4<br>5　6　7　8</span></span><strong><?php echo esc_html( $label ); ?></strong></label>
		<?php endforeach; ?></fieldset></section><section class="ttz-setting-group"><h3><?php esc_html_e( 'Countdown and opening time', 'tuerchenzeit' ); ?></h3>
		<p><label><input type="checkbox" name="ttz_settings[countdown]" value="1" <?php checked( $s['countdown'] ); ?>> <?php esc_html_e( 'Show countdown above the calendar', 'tuerchenzeit' ); ?></label></p>
		<p><label for="ttz-countdown-text"><?php esc_html_e( 'Countdown text', 'tuerchenzeit' ); ?></label><br><input class="large-text" id="ttz-countdown-text" name="ttz_settings[text]" value="<?php echo esc_attr( $s['text'] ); ?>"></p>
		<p><?php echo esc_html( sprintf( __( 'Opening time: midnight in the WordPress timezone (%s). Past doors remain available.', 'tuerchenzeit' ), wp_timezone_string() ) ); ?></p>
		<aside class="ttz-timezone"><strong><?php esc_html_e( 'One opening time for every visitor', 'tuerchenzeit' ); ?></strong><p><?php echo esc_html( sprintf( __( 'All doors open worldwide at the same moment: midnight in the WordPress timezone %s. A visitor’s local date and time can differ.', 'tuerchenzeit' ), wp_timezone_string() ) ); ?></p>
		<?php $opening = Calendar::opens( (int) $s['year'], 1 ); ?>
		<p><?php echo esc_html( wp_date( 'j. F Y, H:i', $opening, wp_timezone() ) . ' (' . wp_timezone_string() . ')' ); ?><br><?php echo esc_html( wp_date( 'j. F Y, H:i', $opening, new \DateTimeZone( 'America/New_York' ) ) . ' (New York)' ); ?></p></aside>
		</section><?php self::presentation_fields( $s ); Guide::controls( $post ); ?><section class="ttz-setting-group"><h3><?php esc_html_e( 'Display and embedding', 'tuerchenzeit' ); ?></h3>
		<div class="ttz-fields"><label><?php esc_html_e( 'Notice for locked or unavailable doors', 'tuerchenzeit' ); ?><select name="ttz_settings[locked_notice]"><option value="inline" <?php selected( $s['locked_notice'], 'inline' ); ?>><?php esc_html_e( 'Text above the calendar', 'tuerchenzeit' ); ?></option><option value="popup" <?php selected( $s['locked_notice'], 'popup' ); ?>><?php esc_html_e( 'Small popup', 'tuerchenzeit' ); ?></option></select></label>
		<label><?php esc_html_e( 'Editor for all own door content on this website', 'tuerchenzeit' ); ?><select name="ttz_editor"><option value="classic" <?php selected( get_option( 'ttz_editor', 'gutenberg' ), 'classic' ); ?>><?php esc_html_e( 'Classic editor', 'tuerchenzeit' ); ?></option><option value="gutenberg" <?php selected( get_option( 'ttz_editor', 'gutenberg' ), 'gutenberg' ); ?>><?php esc_html_e( 'Block Editor (Gutenberg)', 'tuerchenzeit' ); ?></option></select></label></div>
		<p class="description"><?php esc_html_e( 'The editor choice applies to this website. Existing contents are preserved. Blocks can be kept together in the Classic editor, but should be edited with Block Editor (Gutenberg).', 'tuerchenzeit' ); ?></p>
		<p><strong><?php esc_html_e( 'Embed calendar', 'tuerchenzeit' ); ?></strong> <code>[tuerchenzeit id="<?php echo esc_attr( $post->ID ); ?>"]</code> <button type="button" class="button button-small" data-ttz-copy><?php esc_html_e( 'Copy shortcode', 'tuerchenzeit' ); ?></button> <span class="ttz-copy-status" role="status"></span></p>
		<p><strong><?php esc_html_e( 'Countdown only', 'tuerchenzeit' ); ?></strong> <code>[tuerchenzeit_countdown id="<?php echo esc_attr( $post->ID ); ?>"]</code> <button type="button" class="button button-small" data-ttz-copy><?php esc_html_e( 'Copy shortcode', 'tuerchenzeit' ); ?></button> <span class="ttz-copy-status" role="status"></span></p>
		<p><strong><?php esc_html_e( 'Administrator preview', 'tuerchenzeit' ); ?></strong> <code>[tuerchenzeit id="<?php echo esc_attr( $post->ID ); ?>" preview="1"]</code> <button type="button" class="button button-small" data-ttz-copy><?php esc_html_e( 'Copy shortcode', 'tuerchenzeit' ); ?></button> <span class="ttz-copy-status" role="status"></span></p>
		<p><?php esc_html_e( 'Preview requires a logged-in administrator and includes your own drafts. Visitors always see the real opening dates and only published contents.', 'tuerchenzeit' ); ?></p>
		</section><?php if ( 'auto-draft' !== $post->post_status ) : ?>
		<div class="ttz-preparation"><h3><?php esc_html_e( 'Your Advent website', 'tuerchenzeit' ); ?></h3><p><?php esc_html_e( 'Prepare a complete editable Block Editor (Gutenberg) landing page, or create next year’s edition without changing the current contents.', 'tuerchenzeit' ); ?></p>
		<p><?php Preparation::website_actions( $post ); ?> <a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ttz_edition&calendar=' . $post->ID ), 'ttz_edition_' . $post->ID ) ); ?>"><?php esc_html_e( 'Prepare next year', 'tuerchenzeit' ); ?></a></p><?php Preparation::checklist( $post ); ?></div>
		<?php endif; ?>
		<?php if ( $s['next'] && get_post_type( $s['next'] ) === Calendar::TYPE ) : ?>
		<p><a href="<?php echo esc_url( get_edit_post_link( $s['next'], 'raw' ) ); ?>"><?php esc_html_e( 'Edit prepared next edition', 'tuerchenzeit' ); ?></a></p>
		<p><label><?php esc_html_e( 'Switch to the next edition at this WordPress local time', 'tuerchenzeit' ); ?><br><input type="datetime-local" name="ttz_settings[switch_date]" value="<?php echo esc_attr( $s['switch_date'] ); ?>"></label></p><p class="description"><?php esc_html_e( 'The switch only happens after the next calendar is published. An empty date disables the switch. Publish the copied own contents separately when they are ready.', 'tuerchenzeit' ); ?></p>
		<?php endif; ?>
		<?php
	}

	/** Render all 24 assignments with a bounded on-demand content search.
	 * @param \WP_Post $post Calendar being edited.
	 * @return void
	 */
	public static function doors_box( \WP_Post $post ): void {
		$doors = Calendar::doors( $post->ID );
		echo '<p><a href="#ttz-door-1" data-next-unfinished><span class="ttz-live-progress" role="status"></span></a></p><p>' . esc_html__( 'Choose a card to assign content. Search results appear directly below the search field. Assignments are saved with the calendar.', 'tuerchenzeit' ) . '</p><p><a href="#ttz-door-1" data-next-unfinished>' . esc_html__( 'Go through unfinished doors', 'tuerchenzeit' ) . '</a> <span class="ttz-jump-status" role="status"></span></p><p class="ttz-assignment-status" role="status"></p><div class="ttz-admin-grid">';
		for ( $n = 1; $n <= 24; ++$n ) {
			$d = wp_parse_args( $doors[ $n ] ?? array(), array( 'source' => 'own', 'id' => 0, 'mode' => 'dialog', 'image' => 0 ) );
			$entry = $d['id'] ? get_post( $d['id'] ) : null;
			$valid = $entry && Calendar::resolve( $d );
			$status = ! $entry ? __( 'Not assigned', 'tuerchenzeit' ) : ( $valid ? __( 'Ready', 'tuerchenzeit' ) : __( 'Not available — check publication', 'tuerchenzeit' ) );
			?>
			<article id="ttz-door-<?php echo esc_attr( $n ); ?>" tabindex="-1" class="ttz-admin-card" data-ready="<?php echo $valid ? 'ready' : ( $entry ? 'draft' : 'empty' ); ?>" data-card="<?php echo esc_attr( $n ); ?>">
			<div class="ttz-card-top"><span class="ttz-card-day"><?php echo esc_html( sprintf( '%02d', $n ) ); ?></span><span data-card-status><?php echo esc_html( $status ); ?></span></div>
			<h3 data-card-title><?php echo esc_html( $entry ? get_the_title( $entry ) : __( 'Not assigned', 'tuerchenzeit' ) ); ?></h3>
			<p class="description" data-card-source><?php echo esc_html( 'reference' === $d['source'] ? __( 'Existing entry', 'tuerchenzeit' ) : __( 'Own door content', 'tuerchenzeit' ) ); ?></p>
			<?php foreach ( array( 'source', 'id', 'mode', 'image' ) as $field ) : ?><input type="hidden" data-field="<?php echo esc_attr( $field ); ?>" name="ttz_doors[<?php echo esc_attr( $n ); ?>][<?php echo esc_attr( $field ); ?>]" value="<?php echo esc_attr( $d[ $field ] ); ?>"><?php endforeach; ?>
			<div class="ttz-card-image"><?php if ( ! empty( $d['image'] ) ) { echo wp_get_attachment_image( absint( $d['image'] ), 'thumbnail' ); } ?></div><p><button type="button" class="button-link" data-choose-image><?php esc_html_e( 'Choose door image', 'tuerchenzeit' ); ?></button> · <button type="button" class="button-link" data-clear-image><?php esc_html_e( 'Remove image', 'tuerchenzeit' ); ?></button></p><button type="button" class="button" data-pick><?php esc_html_e( 'Choose content', 'tuerchenzeit' ); ?></button>
			<div class="ttz-card-actions"><a data-card-edit target="_blank" rel="noopener" <?php echo $entry ? 'href="' . esc_url( get_edit_post_link( $entry->ID, 'raw' ) ) . '"' : 'hidden'; ?>><?php esc_html_e( 'Edit assigned content', 'tuerchenzeit' ); ?></a><button type="button" class="button-link-delete" data-remove <?php echo $entry ? '' : 'hidden'; ?>><?php esc_html_e( 'Remove assignment', 'tuerchenzeit' ); ?></button></div></article>
			<?php
		}
		echo '</div>';
	}


	/** Show optional styling and limited template controls.
	 * @param array $settings Current calendar settings.
	 * @return void
	 */
	public static function presentation_fields( array $settings ): void {
		?>
		<details class="ttz-options"><summary><?php esc_html_e( 'Popup and button appearance', 'tuerchenzeit' ); ?></summary>
		<p><?php esc_html_e( 'Optional overrides for this calendar. Empty color and opacity fields use the design defaults. Text on popup backgrounds is adjusted automatically.', 'tuerchenzeit' ); ?></p><div class="ttz-fields">
		<?php foreach ( array( 'modal_bg' => __( 'Popup background', 'tuerchenzeit' ), 'christmas_bg' => __( 'December 24 popup background (optional)', 'tuerchenzeit' ), 'overlay_color' => __( 'Overlay color', 'tuerchenzeit' ), 'button_bg' => __( 'Button background', 'tuerchenzeit' ), 'button_fg' => __( 'Button text', 'tuerchenzeit' ), 'button_border' => __( 'Button border', 'tuerchenzeit' ) ) as $key => $label ) : ?>
		<div class="ttz-color-field"><label for="ttz-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label><input data-optional-color type="text" id="ttz-<?php echo esc_attr( $key ); ?>" name="ttz_settings[<?php echo esc_attr( $key ); ?>]" pattern="#[0-9a-fA-F]{6}" placeholder="#235b4e" value="<?php echo esc_attr( $settings[ $key ] ); ?>"><span data-color-root></span></div>
		<?php endforeach; ?>
		<label><?php esc_html_e( 'Overlay opacity (%)', 'tuerchenzeit' ); ?><input type="number" min="0" max="100" step="1" placeholder="79" name="ttz_settings[overlay_opacity]" value="<?php echo esc_attr( $settings['overlay_opacity'] ); ?>"></label>
		<label><?php esc_html_e( 'Button style', 'tuerchenzeit' ); ?><select name="ttz_settings[button_style]"><?php foreach ( array( 'default' => __( 'Design default', 'tuerchenzeit' ), 'outline' => __( 'Outline', 'tuerchenzeit' ), 'filled' => __( 'Filled', 'tuerchenzeit' ) ) as $key => $label ) : ?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['button_style'], $key ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></label>
		<label><?php esc_html_e( 'Button corners', 'tuerchenzeit' ); ?><select name="ttz_settings[button_shape]"><?php foreach ( array( 'default' => __( 'Design default', 'tuerchenzeit' ), 'square' => __( 'Square', 'tuerchenzeit' ), 'soft' => __( 'Softly rounded', 'tuerchenzeit' ), 'pill' => __( 'Pill', 'tuerchenzeit' ) ) as $key => $label ) : ?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['button_shape'], $key ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></label></div>
		<p><label><?php esc_html_e( 'Full content button label', 'tuerchenzeit' ); ?><input class="large-text" name="ttz_settings[button_label]" value="<?php echo esc_attr( $settings['button_label'] ); ?>"></label></p>
		<p><?php esc_html_e( 'These button settings apply to all linked entries opened in a popup. Check the text contrast after changing button colors.', 'tuerchenzeit' ); ?></p><div class="ttz-popup-preview"><div class="ttz-preview-toolbar"><strong><?php esc_html_e( 'Schematic live preview', 'tuerchenzeit' ); ?></strong><label><input type="checkbox" data-preview-christmas> <?php esc_html_e( 'December 24', 'tuerchenzeit' ); ?></label></div><p class="description"><?php esc_html_e( 'Sample content only. Changes appear here before saving; the public calendar stays unchanged.', 'tuerchenzeit' ); ?></p><div class="ttz-preview-stage"><div class="ttz-preview-calendar" aria-hidden="true"><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span><span>10</span><span>11</span><span>12</span><span>13</span><span>14</span><span>15</span><span>16</span><span>17</span><span>18</span></div><div class="ttz-preview-overlay"></div><div class="ttz-preview-modal"><span class="ttz-preview-close" aria-hidden="true">×</span><h4><?php esc_html_e( 'A moment of anticipation', 'tuerchenzeit' ); ?></h4><div class="ttz-preview-lines" aria-hidden="true"><i></i><i></i><i></i></div><span class="ttz-preview-button"><?php esc_html_e( 'View full entry', 'tuerchenzeit' ); ?></span></div></div></div></details>
		<details class="ttz-options"><summary><?php esc_html_e( 'Messages for closed or empty doors', 'tuerchenzeit' ); ?></summary><p><?php esc_html_e( 'Paragraphs, bold and italic only. Placeholders use the WordPress timezone. Unknown placeholders remain unchanged.', 'tuerchenzeit' ); ?></p>
		<?php foreach ( array( 'notice_locked' => __( 'Not yet unlocked', 'tuerchenzeit' ), 'notice_missing' => __( 'No content assigned', 'tuerchenzeit' ) ) as $key => $label ) : ?>
		<div data-notice-editor><p><label for="ttz-<?php echo esc_attr( $key ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label></p><div class="ttz-notice-tools">
		<?php foreach ( array( '{tuerchen}', '{tag}', '{datum}', '{uhrzeit}' ) as $token ) : ?><button type="button" class="button" data-insert="<?php echo esc_attr( $token ); ?>"><?php echo esc_html( $token ); ?></button> <?php endforeach; ?>
		<button type="button" class="button" data-format="strong"><?php esc_html_e( 'Bold', 'tuerchenzeit' ); ?></button> <button type="button" class="button" data-format="em"><?php esc_html_e( 'Italic', 'tuerchenzeit' ); ?></button></div>
		<textarea class="large-text" rows="3" id="ttz-<?php echo esc_attr( $key ); ?>" name="ttz_settings[<?php echo esc_attr( $key ); ?>]" data-example-door="<?php echo esc_attr( sprintf( __( 'Door %d', 'tuerchenzeit' ), 4 ) ); ?>" data-example-date="<?php echo esc_attr( wp_date( 'j. F', Calendar::opens( (int) $settings['year'], 4 ) ) ); ?>"><?php echo esc_textarea( $settings[ $key ] ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Example preview: door 4', 'tuerchenzeit' ); ?></p><div class="ttz-notice-preview" data-notice-preview></div></div>
		<?php endforeach; ?></details>
		<?php
	}

	/** Save only explicit, authorized fields; reject invalid cross-type assignments.
	 * @param int $id Calendar ID.
	 * @param \WP_Post $post Saved calendar.
	 * @return void
	 */
	public static function save( int $id, \WP_Post $post ): void {
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $id ) || ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_post', $id ) || ! isset( $_POST['ttz_nonce'] ) || ! is_string( $_POST['ttz_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ttz_nonce'] ) ), 'ttz_save_' . $id ) ) { return; }
		$old = Calendar::settings( $id );
		$s = isset( $_POST['ttz_settings'] ) && is_array( $_POST['ttz_settings'] ) ? wp_unslash( $_POST['ttz_settings'] ) : array();
		$year = is_scalar( $s['year'] ?? null ) ? absint( $s['year'] ) : (int) wp_date( 'Y' );
		$theme = is_string( $s['theme'] ?? null ) ? $s['theme'] : 'evergreen';
		update_post_meta( $id, '_ttz_settings', array(
			'year' => max( 2000, min( 2199, $year ) ),
			'countdown' => empty( $s['countdown'] ) ? 0 : 1,
			'text' => is_string( $s['text'] ?? null ) ? sanitize_text_field( $s['text'] ) : __( 'The first door opens in', 'tuerchenzeit' ),
			'color' => is_string( $s['color'] ?? null ) ? ( sanitize_hex_color( $s['color'] ) ?: '#235b4e' ) : '#235b4e',
			'locked_notice' => 'popup' === ( $s['locked_notice'] ?? '' ) ? 'popup' : 'inline',
			'next' => $old['next'],
			'switch_date' => isset( $s['switch_date'] ) && is_string( $s['switch_date'] ) ? ( Calendar::switch_time( $s['switch_date'] ) ? $s['switch_date'] : '' ) : $old['switch_date'],
			'theme' => in_array( $theme, array( 'evergreen', 'village', 'winter', 'bethlehem', 'arrival', 'starpost' ), true ) ? $theme : 'evergreen',
		) + Presentation::sanitize( $s, $old ) + Season::sanitize( $s, $old ) );
		if ( isset( $_POST['ttz_editor'] ) && in_array( $_POST['ttz_editor'], array( 'classic', 'gutenberg' ), true ) ) { update_option( 'ttz_editor', $_POST['ttz_editor'] ); }
		$input = isset( $_POST['ttz_doors'] ) && is_array( $_POST['ttz_doors'] ) ? wp_unslash( $_POST['ttz_doors'] ) : array();
		$doors = array();
		for ( $n = 1; $n <= 24; ++$n ) {
			$d = isset( $input[ $n ] ) && is_array( $input[ $n ] ) ? $input[ $n ] : array();
			$door = array( 'source' => 'reference' === ( $d['source'] ?? '' ) ? 'reference' : 'own', 'id' => is_scalar( $d['id'] ?? null ) ? absint( $d['id'] ) : 0, 'mode' => 'link' === ( $d['mode'] ?? '' ) ? 'link' : 'dialog', 'image' => is_scalar( $d['image'] ?? null ) && wp_attachment_is_image( absint( $d['image'] ) ) ? absint( $d['image'] ) : 0 );
			// Preserve existing assignments if the linked content temporarily becomes unavailable.
			$own = 'own' === $door['source'] ? get_post( $door['id'] ) : null;
			$editable_draft = $own && Calendar::DOOR === $own->post_type && in_array( $own->post_status, array( 'draft', 'pending', 'private', 'publish' ), true );
			if ( $door['id'] && ! $editable_draft && ! Calendar::resolve( $door ) ) {
				$previous = Calendar::doors( $id )[ $n ] ?? array();
				if ( $door !== $previous ) { $door['id'] = 0; }
			}
			$doors[ $n ] = $door;
		}
		update_post_meta( $id, '_ttz_doors', $doors );
	}

	/** Search published content with both CSRF and capability protection.
	 * @return void
	 */
	public static function search(): void {
		if ( ! current_user_can( 'manage_options' ) ) { wp_send_json_error( null, 403 ); }
		check_ajax_referer( 'ttz_search', 'nonce' );
		$q = isset( $_POST['q'] ) && is_string( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';
		$source = isset( $_POST['source'] ) && 'reference' === $_POST['source'] ? 'reference' : 'own';
		$types = 'own' === $source ? array( Calendar::DOOR ) : array_values( array_filter( get_post_types( array(), 'names' ), static function ( string $type ): bool {
			return ! in_array( $type, array( Calendar::TYPE, Calendar::DOOR, 'attachment' ), true ) && is_post_type_viewable( $type );
		} ) );
		if ( strlen( $q ) < 2 || ! $types ) { wp_send_json_success( array() ); }
		$type = isset( $_POST['type'] ) && is_string( $_POST['type'] ) ? sanitize_key( $_POST['type'] ) : '';
		if ( 'reference' === $source && in_array( $type, $types, true ) ) { $types = array( $type ); }
		$query = new \WP_Query( array( 'post_type' => $types, 'post_status' => 'own' === $source ? array( 'publish', 'draft', 'pending', 'private' ) : 'publish', 'has_password' => false, 's' => $q, 'search_columns' => array( 'post_title' ), 'posts_per_page' => 20, 'no_found_rows' => true, 'orderby' => 'title', 'order' => 'ASC' ) );
		$result = array();
		foreach ( $query->posts as $entry ) {
			$result[] = array( 'id' => $entry->ID, 'title' => get_the_title( $entry ) . ' (#' . $entry->ID . ')', 'type' => get_post_type_object( $entry->post_type )->labels->singular_name, 'status' => $entry->post_status, 'edit' => get_edit_post_link( $entry->ID, 'raw' ) );
		}
		wp_send_json_success( $result );
	}

	/** Add the primary management link before the deactivate link.
	 * @param array $links Existing plugin links.
	 * @return array Updated links.
	 */
	public static function links( array $links ): array {
		if ( current_user_can( 'manage_options' ) && ! is_network_admin() ) { array_unshift( $links, '<a href="' . esc_url( admin_url( 'edit.php?post_type=' . Calendar::TYPE ) ) . '">' . esc_html__( 'Settings', 'tuerchenzeit' ) . '</a>' ); }
		return $links;
	}

	/** Show scoped branding, local help and a structured change history.
	 * @return void
	 */
	public static function footer(): void {
		$screen = get_current_screen();
		if ( ! $screen || ( ! in_array( $screen->post_type, array( Calendar::TYPE, Calendar::DOOR ), true ) && ! str_contains( $screen->id, 'ttz-guide' ) ) ) { return; }
		?>
		<?php if ( Calendar::TYPE === $screen->post_type ) { self::picker(); } ?>
		<div class="ttz-admin-footer"><picture class="ttz-footer-icon"><source type="image/svg+xml" srcset="<?php echo esc_url( plugins_url( 'assets/brand-icon.svg', FILE ) ); ?>"><img src="<?php echo esc_url( plugins_url( 'assets/brand-icon-128.png', FILE ) ); ?>" width="56" height="56" alt=""></picture><div><p><strong>Türchenzeit</strong> · <?php echo esc_html( Branding::text( 'slogan' ) ); ?></p><p><?php echo esc_html( VERSION ); ?> · <a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( Branding::documentation_url() ); ?>" data-ttz-help><?php esc_html_e( 'Documentation', 'tuerchenzeit' ); ?></a> · <a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( plugins_url( str_starts_with( determine_locale(), 'de_' ) ? 'CHANGELOG-de.md' : 'CHANGELOG.md', FILE ) ); ?>" data-ttz-changelog><?php esc_html_e( 'Changelog', 'tuerchenzeit' ); ?></a></p><p><?php esc_html_e( 'A project by David Decker – DECKERWEB · Inspiration', 'tuerchenzeit' ); ?></p></div></div>
		<dialog class="ttz-admin-dialog ttz-documentation-dialog" id="ttz-help" hidden aria-labelledby="ttz-help-title"><header class="ttz-documentation-header"><h2 id="ttz-help-title"><?php esc_html_e( 'Documentation', 'tuerchenzeit' ); ?></h2><form method="dialog"><button autofocus aria-label="<?php esc_attr_e( 'Close', 'tuerchenzeit' ); ?>">×</button></form><a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( Branding::documentation_url() ); ?>"><?php esc_html_e( 'Open documentation in a new tab', 'tuerchenzeit' ); ?></a></header><iframe class="ttz-documentation-frame" title="<?php esc_attr_e( 'Documentation', 'tuerchenzeit' ); ?>" src="<?php echo esc_url( Branding::documentation_url() ); ?>" loading="lazy" sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox"></iframe></dialog>
		<dialog class="ttz-admin-dialog" id="ttz-changelog" hidden aria-labelledby="ttz-changelog-title"><form method="dialog"><button aria-label="<?php esc_attr_e( 'Close', 'tuerchenzeit' ); ?>">×</button></form><h2 id="ttz-changelog-title"><?php esc_html_e( 'Changelog', 'tuerchenzeit' ); ?></h2><h3><?php echo esc_html( VERSION ); ?> · 2026-10-21</h3><p><strong class="ttz-badge"><?php esc_html_e( 'New:', 'tuerchenzeit' ); ?></strong></p><ul><li><?php esc_html_e( '24 daily doors with dedicated private content or linked posts and pages.', 'tuerchenzeit' ); ?></li><li><?php esc_html_e( 'Six Advent designs, accent colors, adjustable dialogs, buttons and notices.', 'tuerchenzeit' ); ?></li><li><?php esc_html_e( 'An editable standalone Advent landing page, optional header/footer patterns and local fonts.', 'tuerchenzeit' ); ?></li><li><?php esc_html_e( 'Readiness checks, a private date preview and independent annual editions.', 'tuerchenzeit' ); ?></li><li><?php esc_html_e( 'JSON and ZIP imports for day sets, including raster images.', 'tuerchenzeit' ); ?></li><li><?php esc_html_e( 'Optional original-content protection, click-to-load YouTube videos and Daily Scripture integration.', 'tuerchenzeit' ); ?></li><li><?php esc_html_e( 'Single-site and Multisite support, with independent settings for each website.', 'tuerchenzeit' ); ?></li></ul></dialog>
		<?php
	}
	/** Add direct access to new content and separate the two menu groups visually.
	 * @return void
	 */
	public static function menu(): void {
		add_submenu_page( 'edit.php?post_type=' . Calendar::TYPE, __( 'New door content', 'tuerchenzeit' ), __( 'New door content', 'tuerchenzeit' ), 'manage_options', 'post-new.php?post_type=' . Calendar::DOOR );
	}

	/** Create an administrator-owned draft for immediate assignment, without publishing it.
	 * @return void
	 */
	public static function create_door(): void {
		if ( ! current_user_can( 'manage_options' ) ) { wp_send_json_error( null, 403 ); }
		check_ajax_referer( 'ttz_search', 'nonce' );
		$title = isset( $_POST['title'] ) && is_string( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		if ( '' === $title ) { wp_send_json_error( array( 'message' => __( 'Enter a title for the new content.', 'tuerchenzeit' ) ), 400 ); }
		$id = wp_insert_post( array( 'post_type' => Calendar::DOOR, 'post_title' => $title, 'post_status' => 'draft' ), true );
		if ( is_wp_error( $id ) ) { wp_send_json_error( array( 'message' => __( 'The content could not be created.', 'tuerchenzeit' ) ), 500 ); }
		wp_send_json_success( array( 'id' => $id, 'title' => $title, 'status' => 'draft', 'edit' => get_edit_post_link( $id, 'raw' ) ) );
	}

	/** Render one accessible chooser shared by all 24 cards.
	 * @return void
	 */
	public static function picker(): void {
		?>
		<dialog id="ttz-picker" class="ttz-admin-dialog ttz-picker" hidden aria-labelledby="ttz-picker-title">
		<form method="dialog"><button aria-label="<?php esc_attr_e( 'Close', 'tuerchenzeit' ); ?>">×</button></form>
		<h2 id="ttz-picker-title"><?php esc_html_e( 'Choose content', 'tuerchenzeit' ); ?></h2>
		<div class="ttz-fields"><label><?php esc_html_e( 'Content source', 'tuerchenzeit' ); ?><select data-picker-source><option value="own"><?php esc_html_e( 'Own door content', 'tuerchenzeit' ); ?></option><option value="reference"><?php esc_html_e( 'Existing entry', 'tuerchenzeit' ); ?></option></select></label>
		<label data-type-wrap hidden><?php esc_html_e( 'Content type', 'tuerchenzeit' ); ?><select data-picker-type><option value=""><?php esc_html_e( 'All public content types', 'tuerchenzeit' ); ?></option>
		<?php foreach ( get_post_types( array(), 'objects' ) as $type ) : if ( ! is_post_type_viewable( $type ) || 'attachment' === $type->name ) { continue; } ?>
		<option value="<?php echo esc_attr( $type->name ); ?>"><?php echo esc_html( $type->labels->singular_name ); ?></option><?php endforeach; ?></select></label></div>
		<label class="ttz-picker-search"><?php esc_html_e( 'Search by title', 'tuerchenzeit' ); ?><input type="search" data-picker-search autocomplete="off" placeholder="<?php esc_attr_e( 'At least two characters', 'tuerchenzeit' ); ?>"></label>
		<p role="status" data-picker-status></p><ul class="ttz-search-results" data-picker-results></ul>
		<label data-mode-wrap hidden><?php esc_html_e( 'Open existing entry', 'tuerchenzeit' ); ?><select data-picker-mode><option value="dialog"><?php esc_html_e( 'Preview in a dialog', 'tuerchenzeit' ); ?></option><option value="link"><?php esc_html_e( 'Go to entry', 'tuerchenzeit' ); ?></option></select></label>
		<div class="ttz-create-own" data-create-wrap><p><?php esc_html_e( 'Or create new content with the title entered above and assign it to this door.', 'tuerchenzeit' ); ?></p><button type="button" class="button" data-create-own><?php esc_html_e( 'Create and assign own content', 'tuerchenzeit' ); ?></button></div>
		</dialog>
		<?php
	}

}
