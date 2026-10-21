<?php
/** Safe per-calendar presentation and limited notice templates. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Keeps presentation overrides and notice formatting on a narrow allowlist. */
final class Presentation {
	/** Return optional settings and translated notice defaults. @return array Defaults. */
	public static function defaults(): array {
		return array( 'modal_bg' => '', 'christmas_bg' => '', 'overlay_color' => '', 'overlay_opacity' => '', 'button_bg' => '', 'button_fg' => '', 'button_border' => '', 'button_shape' => 'default', 'button_style' => 'default', 'button_label' => __( 'View full entry', 'tuerchenzeit' ), 'notice_locked' => __( 'A little anticipation: {tuerchen} opens on {datum} at {uhrzeit}.', 'tuerchenzeit' ), 'notice_missing' => __( 'A little surprise for {tuerchen} is still being prepared.', 'tuerchenzeit' ) );
	}
	/** Strip everything except paragraphs and emphasis, including attributes.
	 * @param string $text Notice template or rendered notice.
	 * @return string Safe limited HTML.
	 */
	public static function clean_notice( string $text ): string {
		return wp_kses( $text, array( 'p' => array(), 'br' => array(), 'strong' => array(), 'em' => array() ) );
	}
	/** Validate overrides, preserving older settings when no new field was submitted.
	 * @param array $input Submitted settings.
	 * @param array $old Stored settings.
	 * @return array Sanitized presentation settings.
	 */
	public static function sanitize( array $input, array $old ): array {
		$out = array_intersect_key( $old, self::defaults() ) + self::defaults();
		foreach ( array( 'modal_bg', 'christmas_bg', 'overlay_color', 'button_bg', 'button_fg', 'button_border' ) as $key ) {
			if ( array_key_exists( $key, $input ) ) { $out[ $key ] = is_string( $input[ $key ] ) ? ( sanitize_hex_color( $input[ $key ] ) ?: '' ) : ''; }
		}
		if ( array_key_exists( 'overlay_opacity', $input ) ) { $v = $input['overlay_opacity']; $out['overlay_opacity'] = is_scalar( $v ) && '' !== (string) $v && is_numeric( $v ) ? max( 0, min( 100, (int) $v ) ) : ''; }
		foreach ( array( 'button_shape' => array( 'default', 'square', 'soft', 'pill' ), 'button_style' => array( 'default', 'outline', 'filled' ) ) as $key => $allowed ) {
			if ( isset( $input[ $key ] ) ) { $out[ $key ] = in_array( $input[ $key ], $allowed, true ) ? $input[ $key ] : 'default'; }
		}
		if ( isset( $input['button_label'] ) && is_string( $input['button_label'] ) ) { $out['button_label'] = sanitize_text_field( $input['button_label'] ) ?: self::defaults()['button_label']; }
		foreach ( array( 'notice_locked', 'notice_missing' ) as $key ) {
			if ( isset( $input[ $key ] ) && is_string( $input[ $key ] ) ) { $out[ $key ] = trim( self::clean_notice( $input[ $key ] ) ); if ( '' === trim( wp_strip_all_tags( $out[ $key ] ) ) ) { $out[ $key ] = self::defaults()[ $key ]; } }
		}
		return $out;
	}
	/** Render only public scheduling information; never reveals a door title or content.
	 * @param array $settings Calendar settings.
	 * @param int $day Door number, 1–24.
	 * @param bool $open Whether its scheduled opening has passed.
	 * @return string Safe formatted notice.
	 */
	public static function notice( array $settings, int $day, bool $open ): string {
		$time = Calendar::opens( (int) $settings['year'], $day );
		$values = array( '{tuerchen}' => sprintf( __( 'Door %d', 'tuerchenzeit' ), $day ), '{tag}' => (string) $day, '{datum}' => wp_date( 'j. F', $time, wp_timezone() ), '{uhrzeit}' => wp_date( 'H:i', $time, wp_timezone() ) );
		return wpautop( self::clean_notice( strtr( (string) $settings[ $open ? 'notice_missing' : 'notice_locked' ], array_map( 'esc_html', $values ) ) ) );
	}
}
