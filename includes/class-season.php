<?php
/** Seasonal options, deterministic door order and public presentation state. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Validates optional seasonal features without changing real public time. */
final class Season {
	/** Return conservative opt-in defaults. @return array Settings. */
	public static function defaults(): array { return array( 'protect_references' => 0, 'today_button' => 1, 'remember' => 0, 'animation' => 'none', 'shuffle' => 0, 'shuffle_seed' => '', 'closing_enabled' => 0, 'closing_text' => __( 'Thank you for sharing this Advent season. Merry Christmas!', 'tuerchenzeit' ) ); }
	/** Sanitize seasonal controls, preserving absent fields from older editor forms.
	 * @param array $input Submitted fields.
	 * @param array $old Old settings.
	 * @return array Validated options.
	 */
	public static function sanitize( array $input, array $old ): array {
		$out = array_intersect_key( $old, self::defaults() ) + self::defaults();
		foreach ( array( 'protect_references', 'today_button', 'remember', 'shuffle', 'closing_enabled' ) as $key ) { if ( array_key_exists( $key, $input ) ) { $out[ $key ] = '1' === (string) ( is_scalar( $input[ $key ] ) ? $input[ $key ] : '' ) ? 1 : 0; } }
		if ( isset( $input['animation'] ) ) { $out['animation'] = in_array( $input['animation'], array( 'none', 'fade', 'door' ), true ) ? $input['animation'] : 'none'; }
		if ( isset( $input['closing_text'] ) && is_string( $input['closing_text'] ) ) { $out['closing_text'] = Presentation::clean_notice( substr( $input['closing_text'], 0, 8000 ) ); }
		if ( isset( $input['shuffle_seed'] ) && is_string( $input['shuffle_seed'] ) ) { $out['shuffle_seed'] = $input['shuffle_seed']; }
		if ( ! preg_match( '/^[a-zA-Z0-9-]{16,64}$/D', (string) $out['shuffle_seed'] ) ) { $out['shuffle_seed'] = wp_generate_uuid4(); }
		return $out;
	}
	/** Stable order; never reshuffles during requests or visitor navigation.
	 * @param array $settings Calendar settings.
	 * @return array Door numbers.
	 */
	public static function order( array $settings ): array {
		$days = range( 1, 24 );
		if ( ! empty( $settings['shuffle'] ) ) { $seed = (string) $settings['shuffle_seed']; usort( $days, static fn( $a, $b ) => strcmp( hash( 'sha256', $seed . ':' . $a ), hash( 'sha256', $seed . ':' . $b ) ) ); }
		return $days;
	}
	/** Return day images only after their real or authorized simulated opening.
	 * @param array $door Assignment.
	 * @return array|null Public thumbnail URL and alt text.
	 */
	public static function image( array $door ): ?array {
		$id = absint( $door['image'] ?? 0 ); $url = $id && wp_attachment_is_image( $id ) ? wp_get_attachment_image_url( $id, 'medium' ) : false;
		return $url ? array( 'url' => esc_url_raw( $url ), 'alt' => sanitize_text_field( get_post_meta( $id, '_wp_attachment_image_alt', true ) ) ) : null;
	}
	/** Summarize readiness without publishing or editing contents.
	 * @param int $id Calendar ID.
	 * @return array Missing/unpublished day lists and calendar configuration.
	 */
	public static function readiness( int $id ): array {
		$missing = $drafts = array(); $doors = Calendar::doors( $id );
		for ( $n = 1; $n <= 24; ++$n ) { $d = $doors[ $n ] ?? array(); if ( empty( $d['id'] ) ) { $missing[] = $n; } elseif ( ! Calendar::resolve( $d ) ) { $drafts[] = $n; } }
		$s = Calendar::settings( $id ); return array( 'missing' => $missing, 'unavailable' => $drafts, 'year' => $s['year'], 'timezone' => wp_timezone_string(), 'published' => 'publish' === get_post_status( $id ) );
	}
}
