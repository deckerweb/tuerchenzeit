<?php
/** Calendar storage and server-side publication policy. */
namespace Deckerweb\Tuerchenzeit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Owns site-local post types and resolves safe door content. */
final class Calendar {
	const TYPE = 'ttz_calendar';
	const DOOR = 'ttz_door';

	/** Register private storage types with administrator-only editing. @return void */
	public static function register(): void {
		$common = array(
			'public' => false, 'publicly_queryable' => false, 'show_ui' => true,
			'show_in_rest' => false, 'exclude_from_search' => true, 'rewrite' => false,
			'query_var' => false, 'can_export' => current_user_can( 'manage_options' ), 'map_meta_cap' => false,
			'capabilities' => array_fill_keys( array( 'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'publish_posts', 'read_private_posts', 'delete_posts', 'delete_private_posts', 'delete_published_posts', 'delete_others_posts', 'edit_private_posts', 'edit_published_posts', 'create_posts' ), 'manage_options' ),
		);
		register_post_type( self::TYPE, array_merge( $common, array(
			'labels' => array( 'name' => __( 'Calendars', 'tuerchenzeit' ), 'singular_name' => __( 'Calendar', 'tuerchenzeit' ), 'add_new_item' => __( 'Add calendar', 'tuerchenzeit' ), 'edit_item' => __( 'Edit calendar', 'tuerchenzeit' ), 'menu_name' => 'Türchenzeit', 'all_items' => __( 'All calendars', 'tuerchenzeit' ) ),
			'menu_icon' => 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( dirname( __DIR__ ) . '/assets/menu-door.svg' ) ), 'supports' => array( 'title' ),
		) ) );
		register_post_type( self::DOOR, array_merge( $common, array(
			'labels' => array( 'name' => __( 'Own door content', 'tuerchenzeit' ), 'singular_name' => __( 'Door content', 'tuerchenzeit' ), 'add_new_item' => __( 'Add door content', 'tuerchenzeit' ), 'edit_item' => __( 'Edit door content', 'tuerchenzeit' ) ),
			'show_in_menu' => 'edit.php?post_type=' . self::TYPE,
			'show_in_rest' => true, 'rest_controller_class' => '\\Deckerweb\\Tuerchenzeit\\Door_Controller',
			'supports' => array( 'title', 'editor', 'thumbnail', 'revisions' ),
		) ) );
	}

	/** Get a valid calendar, allowing drafts only for an authorized preview.
	 * @param int $id Calendar ID.
	 * @param bool $preview Whether preview was authorized by the caller.
	 * @return \WP_Post|null Calendar or null.
	 */
	public static function get( int $id, bool $preview = false ): ?\WP_Post {
		if ( $id < 1 ) { return null; }
		$preview = $preview && current_user_can( 'manage_options' ) && current_user_can( 'edit_post', $id );
		$post = get_post( $id );
		if ( ! $post || self::TYPE !== $post->post_type || ( '' !== $post->post_password && ! $preview ) || ( 'publish' !== $post->post_status && ! ( $preview && current_user_can( 'edit_post', $id ) && in_array( $post->post_status, array( 'draft', 'pending', 'future', 'private' ), true ) ) ) ) {
			return null;
		}
		return $post;
	}

	/** Return calendar settings with safe defaults.
	 * @param int $id Calendar ID.
	 * @return array Settings.
	 */
	public static function settings( int $id ): array {
		$data = get_post_meta( $id, '_ttz_settings', true );
		return wp_parse_args( is_array( $data ) ? $data : array(), array( 'year' => (int) wp_date( 'Y' ), 'countdown' => 1, 'text' => __( 'The first door opens in', 'tuerchenzeit' ), 'color' => '#235b4e', 'locked_notice' => 'inline', 'theme' => 'evergreen', 'next' => 0, 'switch_date' => '' ) + Presentation::defaults() + Season::defaults() );
	}

	/** Read the calendar assignments without exposing them publicly.
	 * @param int $id Calendar ID.
	 * @return array Door assignments indexed by day.
	 */
	public static function doors( int $id ): array {
		$data = get_post_meta( $id, '_ttz_doors', true );
		return is_array( $data ) ? $data : array();
	}

	/** Calculate opening timestamp in the site's timezone, including DST.
	 * @param int $year Calendar year.
	 * @param int $day December day, 1–24.
	 * @return int Unix timestamp.
	 */
	public static function opens( int $year, int $day ): int {
		return ( new \DateTimeImmutable( sprintf( '%04d-12-%02d 00:00:00', $year, $day ), wp_timezone() ) )->getTimestamp();
	}

	/** Resolve an assignment; never accept private/password-protected linked posts.
	 * @param array $door Assignment with source and ID.
	 * @param bool $preview Allow own drafts only for an authorized administrator.
	 * @return \WP_Post|null Safe content or null.
	 */
	public static function resolve( array $door, bool $preview = false ): ?\WP_Post {
		$id = absint( $door['id'] ?? 0 );
		if ( $id < 1 ) { return null; }
		$post = get_post( $id );
		if ( $post && 'own' === ( $door['source'] ?? '' ) && self::DOOR === $post->post_type && $preview && current_user_can( 'manage_options' ) && current_user_can( 'edit_post', $post->ID ) && in_array( $post->post_status, array( 'publish', 'draft', 'pending', 'private', 'future' ), true ) ) { return $post; }
		if ( ! $post || 'publish' !== $post->post_status || '' !== $post->post_password ) {
			return null;
		}
		if ( 'own' === ( $door['source'] ?? '' ) ) {
			return self::DOOR === $post->post_type ? $post : null;
		}
		$type = get_post_type_object( $post->post_type );
		return 'reference' === ( $door['source'] ?? '' ) && $type && is_post_type_viewable( $type ) && ! in_array( $post->post_type, array( 'attachment', self::TYPE, self::DOOR ), true ) ? $post : null;
	}

	/** Resolve an open door after checking the calendar and date on the server.
	 * @param int $id Calendar ID.
	 * @param int $day Door number.
	 * @param bool $preview Authorized preview flag.
	 * @param int|null $now Optional timestamp for deterministic policy tests.
	 * @return array|\WP_Error Safe response or generic unavailable error.
	 */
	public static function content( int $id, int $day, bool $preview = false, ?int $now = null ): array|\WP_Error {
		$preview = $preview && current_user_can( 'manage_options' ) && current_user_can( 'edit_post', $id );
		if ( ! $preview ) { $id = self::active_id( $id, $now ); }
		$settings = self::settings( $id );
		if ( ! self::get( $id, $preview ) || $day < 1 || $day > 24 || ( ! $preview && ( $now ?? time() ) < self::opens( (int) $settings['year'], $day ) ) ) {
			return new \WP_Error( 'ttz_unavailable', __( 'This door is not available yet.', 'tuerchenzeit' ) );
		}
		$door = self::doors( $id )[ $day ] ?? array();
		$post = self::resolve( $door, $preview );
		if ( ! $post || ( ! $preview && 'reference' === ( $door['source'] ?? '' ) && Protection::locked( $post->ID, $now ) ) ) {
			return new \WP_Error( 'ttz_unavailable', __( 'This door is not available yet.', 'tuerchenzeit' ) );
		}
		$reference = 'reference' === $door['source'];
		$url = $reference ? get_permalink( $post ) : '';
		$html = $reference ? wpautop( esc_html( wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_excerpt ?: $post->post_content ) ), 55 ) ) ) : Content::render( $post );
		$image = get_the_post_thumbnail( $post, 'large', array( 'class' => 'ttz-dialog-image', 'loading' => 'lazy' ) );
		return array( 'title' => get_the_title( $post ), 'html' => wp_kses_post( $image . $html ), 'url' => $url ? esc_url_raw( $url ) : '', 'direct' => $reference && 'link' === ( $door['mode'] ?? '' ) );
	}
	/** Resolve the published edition in a bounded chain; drafts never replace the live calendar.
	 * @param int $id Stable calendar ID.
	 * @param int|null $now Optional Unix timestamp for deterministic checks.
	 * @return int Effective calendar ID.
	 */
	public static function active_id( int $id, ?int $now = null ): int {
		$seen = array();
		for ( $depth = 0; $depth < 200; ++$depth ) {
			if ( isset( $seen[ $id ] ) ) { break; }
			$seen[ $id ] = true;
			$s = self::settings( $id );
			$next = absint( $s['next'] );
			$date = self::switch_time( (string) $s['switch_date'] );
			if ( ! $next || ! $date || $date > ( $now ?? time() ) || ! self::get( $next ) || isset( $seen[ $next ] ) ) { break; }
			$id = $next;
		}
		return $id;
	}

	/** Parse a strict local switch date in the WordPress timezone.
	 * @param string $value Local date and time, YYYY-MM-DDTHH:MM.
	 * @return int|null Valid timestamp, otherwise null.
	 */
	public static function switch_time( string $value ): ?int {
		$date = \DateTimeImmutable::createFromFormat( '!Y-m-d\\TH:i', $value, wp_timezone() );
		return $date && $date->format( 'Y-m-d\\TH:i' ) === $value ? $date->getTimestamp() : null;
	}

}
