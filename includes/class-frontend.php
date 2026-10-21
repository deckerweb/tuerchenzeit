<?php
/** Cache-safe shortcode rendering and read-only calendar requests. */
namespace Deckerweb\Tuerchenzeit;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Provides progressively loaded calendars without embedding locked content. */
final class Frontend {
	/** Register shortcodes, asset hooks, and read-only AJAX handlers. @return void */
	public static function hooks(): void {
		add_shortcode( 'tuerchenzeit', array( self::class, 'shortcode' ) );
		add_shortcode( 'tuerchenzeit_countdown', array( self::class, 'countdown_shortcode' ) );
		add_shortcode( 'adventskalender', array( self::class, 'shortcode' ) );
		add_shortcode( 'adventskalender_countdown', array( self::class, 'countdown_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'prepare_styles' ) );
		foreach ( array( 'wp_ajax_ttz_calendar', 'wp_ajax_nopriv_ttz_calendar' ) as $hook ) {
			add_action( $hook, array( self::class, 'request' ) );
		}
	}

	/** Enqueue styles before the head for normal post-content shortcodes. @return void */
	public static function prepare_styles(): void {
		$post = get_post();
		if ( $post && preg_match( '/\[(?:tuerchenzeit|adventskalender)(?:_countdown)?\b/', $post->post_content ) ) {
			self::assets();
		}
	}

	/** Enqueue only local assets, also supporting builder-rendered shortcodes.
	 * @return void
	 */
	public static function assets(): void {
		wp_enqueue_style( 'wp-block-library' );
		Fonts::enqueue();
		if ( class_exists( '\\Deckerweb\\DailyScripture\\Core\\Plugin' ) ) { ( new \Deckerweb\DailyScripture\Core\Plugin() )->styles(); }
		wp_enqueue_style( 'tuerchenzeit', plugins_url( 'assets/calendar.css', FILE ), array( 'ttz-fonts' ), VERSION );
		wp_enqueue_script( 'tuerchenzeit', plugins_url( 'assets/calendar.js', FILE ), array(), VERSION, true );
	}

	/** Render the calendar shortcode using WordPress's callback contract.
	 * @param array|string $atts Shortcode attributes.
	 * @return string Calendar markup.
	 */
	public static function shortcode( $atts ): string {
		return self::render( $atts, false );
	}

	/** Render an isolated countdown.
	 * @param array|string $atts Shortcode attributes.
	 * @return string Accessible countdown shell.
	 */
	public static function countdown_shortcode( $atts ): string {
		return self::render( $atts, true );
	}

	/** Render markup with numbers only; dates and content come from uncached requests.
	 * @param array|string $atts Shortcode attributes: id, countdown, preview.
	 * @param bool $only_countdown Render countdown alone.
	 * @return string Calendar markup or empty string for invalid calendars.
	 */
	public static function render( $atts, bool $only_countdown = false ): string {
		$atts = shortcode_atts( array( 'id' => 0, 'countdown' => '', 'preview' => '0', 'heading' => '1', 'date' => '' ), $atts );
		$id = absint( $atts['id'] );
		$preview = '1' === (string) $atts['preview'] && current_user_can( 'manage_options' ) && current_user_can( 'edit_post', $id );
		if ( ! Calendar::get( $id, $preview ) ) { return ''; }
		self::assets();
		$display_id = $preview ? $id : Calendar::active_id( $id );
		$settings = Calendar::settings( $display_id );
		$date = $preview && is_string( $atts['date'] ) && Calendar::switch_time( $atts['date'] ) ? $atts['date'] : '';
		$uid = wp_unique_id( 'ttz-' );
		// Late builder rendering can occur after wp_head; print registered styles through Core.
		ob_start();
		if ( did_action( 'wp_head' ) && ! wp_style_is( 'tuerchenzeit', 'done' ) ) { wp_print_styles( 'tuerchenzeit' ); }
		?>
		<section class="ttz ttz-<?php echo esc_attr( in_array( $settings['theme'] ?? 'evergreen', array( 'evergreen', 'village', 'winter', 'bethlehem', 'arrival', 'starpost' ), true ) ? ( $settings['theme'] ?? 'evergreen' ) : 'evergreen' ); ?>" aria-label="<?php echo esc_attr( get_the_title( $display_id ) ); ?>" data-ttz data-countdown="<?php echo esc_attr( $only_countdown ? '1' : ( '' === $atts['countdown'] ? 'default' : (string) $atts['countdown'] ) ); ?>" data-popup="<?php echo esc_attr( $settings['locked_notice'] ); ?>" data-video-title="<?php esc_attr_e( 'YouTube video', 'tuerchenzeit' ); ?>" data-date="<?php echo esc_attr( $date ); ?>" data-site="<?php echo esc_attr( get_current_blog_id() ); ?>" data-calendar="<?php echo esc_attr( $id ); ?>" data-endpoint="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-preview="<?php echo $preview ? esc_attr( wp_create_nonce( 'ttz_preview_' . $id ) ) : ''; ?>" data-error="<?php esc_attr_e( 'The calendar could not be loaded. Check your connection and try again.', 'tuerchenzeit' ); ?>" data-unavailable="<?php esc_attr_e( 'This door is not available yet.', 'tuerchenzeit' ); ?>" data-available="<?php esc_attr_e( 'Open door', 'tuerchenzeit' ); ?>" data-day-label="<?php esc_attr_e( 'Door', 'tuerchenzeit' ); ?>" data-empty="<?php esc_attr_e( 'No content available.', 'tuerchenzeit' ); ?>" data-color="<?php echo esc_attr( sanitize_hex_color( $settings['color'] ) ?: '#235b4e' ); ?>">
		<?php if ( '0' !== (string) $atts['heading'] ) : ?><header class="ttz-heading"><span aria-hidden="true">✦</span><p><?php echo esc_html( Branding::text( 'slogan' ) ); ?></p><h2><?php echo esc_html( get_the_title( $display_id ) ); ?></h2><?php if ( 'bethlehem' === ( $settings['theme'] ?? '' ) ) : ?><p class="ttz-meaning"><?php esc_html_e( 'Expecting Jesus Christ. Following the light of hope.', 'tuerchenzeit' ); ?></p><?php elseif ( 'arrival' === ( $settings['theme'] ?? '' ) ) : ?><p class="ttz-meaning"><?php esc_html_e( 'Advent means arrival. We await Jesus Christ, our Savior.', 'tuerchenzeit' ); ?></p><?php endif; ?>
		<span class="ttz-year"><?php echo esc_html( $settings['year'] ); ?></span></header><?php endif; ?>
		<?php if ( $preview ) : ?><p class="ttz-preview"><?php echo esc_html( $date ? sprintf( __( 'Private date preview: %1$s (%2$s). Public opening times remain unchanged.', 'tuerchenzeit' ), $date, wp_timezone_string() ) : __( 'Preview: all assigned doors are available to administrators.', 'tuerchenzeit' ) ); ?></p><?php endif; ?>
		<?php if ( $only_countdown || '0' !== (string) $atts['countdown'] ) : ?>
		<div class="ttz-countdown" hidden>
			<p><?php echo esc_html( $settings['text'] ); ?></p>
			<div class="ttz-clock" role="timer" aria-live="off">
			<?php foreach ( array( 'days' => __( 'Days', 'tuerchenzeit' ), 'hours' => __( 'Hours', 'tuerchenzeit' ), 'minutes' => __( 'Minutes', 'tuerchenzeit' ), 'seconds' => __( 'Seconds', 'tuerchenzeit' ) ) as $unit => $label ) : ?>
				<span><strong data-unit="<?php echo esc_attr( $unit ); ?>">00</strong><small><?php echo esc_html( $label ); ?></small></span>
			<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>
		<div class="ttz-status" role="status"><?php esc_html_e( 'Loading calendar…', 'tuerchenzeit' ); ?></div>
		<button class="ttz-retry" type="button" hidden><?php esc_html_e( 'Try again', 'tuerchenzeit' ); ?></button>
		<?php if ( ! $only_countdown ) : ?>
		<div class="ttz-tools"><button type="button" class="ttz-today" hidden><?php esc_html_e( 'Go to today’s door', 'tuerchenzeit' ); ?></button><button type="button" class="ttz-remember" aria-pressed="false" hidden><?php esc_html_e( 'Remember opened doors on this browser', 'tuerchenzeit' ); ?></button><button type="button" class="ttz-clear-visits" hidden><?php esc_html_e( 'Clear visited-door markers', 'tuerchenzeit' ); ?></button><p class="ttz-storage-note" hidden><?php esc_html_e( 'Only saved on this browser, after enabling this option. No account, tracking or transmission.', 'tuerchenzeit' ); ?></p></div><div class="ttz-closing" hidden></div>
		<div class="ttz-grid">
			<?php foreach ( Season::order( $settings ) as $day ) : ?>
			<button type="button" class="ttz-door" data-day="<?php echo esc_attr( $day ); ?>" data-locked="true" aria-label="<?php echo esc_attr( sprintf( __( 'Door %d', 'tuerchenzeit' ), $day ) ); ?>"><span><?php echo esc_html( $day ); ?></span><small aria-hidden="true">✦</small></button>
			<?php endforeach; ?>
		</div>
		<dialog class="ttz-dialog" hidden aria-labelledby="<?php echo esc_attr( $uid . '-title' ); ?>">
			<form method="dialog"><button class="ttz-close" aria-label="<?php esc_attr_e( 'Close', 'tuerchenzeit' ); ?>">×</button></form>
			<h2 id="<?php echo esc_attr( $uid . '-title' ); ?>" tabindex="-1"></h2>
			<div class="ttz-dialog-content"></div>
			<a class="ttz-read-more" hidden><?php esc_html_e( 'View full entry', 'tuerchenzeit' ); ?></a>
		</dialog>
		<?php endif; ?>
		<noscript><p><?php esc_html_e( 'Please enable JavaScript to open the calendar and display the countdown.', 'tuerchenzeit' ); ?></p></noscript>
		</section>
		<?php
		return ob_get_clean();
	}

	/** Serve public status or one open door. Preview requires both capability and nonce.
	 * Read-only public requests intentionally need no nonce; no cookies or user data are returned.
	 * @return void
	 */
	public static function request(): void {
		nocache_headers();
		header( 'Cache-Control: no-store, private, max-age=0' );
		$id = absint( $_POST['calendar'] ?? 0 );
		$token = isset( $_POST['preview'] ) && is_string( $_POST['preview'] ) ? sanitize_text_field( wp_unslash( $_POST['preview'] ) ) : '';
		$preview = '' !== $token && current_user_can( 'manage_options' ) && current_user_can( 'edit_post', $id ) && wp_verify_nonce( $token, 'ttz_preview_' . $id );
		if ( '' !== $token && ! $preview ) { wp_send_json_error( array( 'message' => __( 'The preview has expired. Please reload the page.', 'tuerchenzeit' ) ), 403 ); }
		if ( ! Calendar::get( $id, (bool) $preview ) ) { wp_send_json_error( array( 'message' => __( 'Calendar unavailable.', 'tuerchenzeit' ) ), 404 ); }
		$date = $preview && is_string( $_POST['date'] ?? null ) ? Calendar::switch_time( sanitize_text_field( wp_unslash( $_POST['date'] ) ) ) : null;
		$now = $date ?? time();
		$day = absint( $_POST['day'] ?? 0 );
		if ( $day ) {
			if ( $date && ( $day > 24 || $now < Calendar::opens( (int) Calendar::settings( $id )['year'], $day ) ) ) { wp_send_json_error( array( 'message' => __( 'This door is not available yet.', 'tuerchenzeit' ) ), 403 ); }
			$result = Calendar::content( $id, $day, (bool) $preview );
			if ( is_wp_error( $result ) ) { wp_send_json_error( array( 'message' => $result->get_error_message() ), 403 ); }
			wp_send_json_success( $result );
		}
		$effective = $preview ? $id : Calendar::active_id( $id );
		$settings = Calendar::settings( $effective );
		$doors = Calendar::doors( $effective );
		// Prime only opened assignments in one query instead of one query per door.
		$opened_ids = array();
		foreach ( $doors as $number => $assignment ) {
			if ( $number >= 1 && $number <= 24 && ( ( $preview && ! $date ) || $now >= Calendar::opens( (int) $settings['year'], (int) $number ) ) ) {
				$assigned_id = absint( $assignment['id'] ?? 0 );
				if ( $assigned_id ) { $opened_ids[] = $assigned_id; }
			}
		}
		if ( $opened_ids ) { _prime_post_caches( array_unique( $opened_ids ), false, false ); }
		$next_change = null;
		$states = array();
		for ( $n = 1; $n <= 24; ++$n ) {
			$opening = Calendar::opens( (int) $settings['year'], $n );
			$open = ( $preview && ! $date ) || $now >= $opening;
			if ( $opening > $now && ( null === $next_change || $opening < $next_change ) ) { $next_change = $opening; }
			$states[] = array( 'day' => $n, 'open' => (bool) $open, 'available' => $open && null !== Calendar::resolve( $doors[ $n ] ?? array(), (bool) $preview ), 'message' => Presentation::notice( $settings, $n, (bool) $open ), 'image' => $open ? Season::image( $doors[ $n ] ?? array() ) : null );
		}
		$closing = Calendar::opens( (int) $settings['year'], 25 );
		if ( $closing > $now && ( null === $next_change || $closing < $next_change ) ) { $next_change = $closing; }
		$today = wp_date( 'Y-m', $now ) === $settings['year'] . '-12' && (int) wp_date( 'j', $now ) <= 24 ? (int) wp_date( 'j', $now ) : 0;
		$switch = Calendar::switch_time( (string) $settings['switch_date'] );
		if ( $settings['next'] && $switch && $switch > $now && ( null === $next_change || $switch < $next_change ) ) { $next_change = $switch; }
		$meaning = 'bethlehem' === $settings['theme'] ? __( 'Expecting Jesus Christ. Following the light of hope.', 'tuerchenzeit' ) : ( 'arrival' === $settings['theme'] ? __( 'Advent means arrival. We await Jesus Christ, our Savior.', 'tuerchenzeit' ) : '' );
		wp_send_json_success( array( 'now' => $now, 'start' => Calendar::opens( (int) $settings['year'], 1 ), 'next' => $next_change, 'doors' => $states, 'order' => Season::order( $settings ), 'today' => ! empty( $settings['today_button'] ) ? $today : 0, 'effective' => $effective, 'remember' => ! empty( $settings['remember'] ), 'animation' => $settings['animation'], 'closing' => ! empty( $settings['closing_enabled'] ) && $now >= $closing ? wpautop( Presentation::clean_notice( $settings['closing_text'] ) ) : '', 'simulated' => null !== $date, 'display' => array( 'title' => get_the_title( $effective ), 'year' => $settings['year'], 'theme' => $settings['theme'], 'color' => $settings['color'], 'text' => $settings['text'], 'countdown' => (bool) $settings['countdown'], 'notice' => $settings['locked_notice'], 'meaning' => $meaning, 'presentation' => Presentation::sanitize( $settings, array() ) ) ) );
	}
}
