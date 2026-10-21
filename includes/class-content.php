<?php
/** Safe block rendering and local YouTube placeholders. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Renders own content without contacting video providers before the visitor chooses to load a player. */
final class Content {
	/** Extract only a valid YouTube video ID from recognized HTTPS/HTTP URL shapes.
	 * @param string $url Submitted video URL.
	 * @return string Valid ID or empty string.
	 */
	public static function video_id( string $url ): string {
		$parts = wp_parse_url( html_entity_decode( trim( $url ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		if ( ! is_array( $parts ) || ! in_array( strtolower( $parts['scheme'] ?? '' ), array( 'https', 'http' ), true ) ) { return ''; }
		$host = strtolower( $parts['host'] ?? '' );
		$id = '';
		if ( in_array( $host, array( 'youtu.be', 'www.youtu.be' ), true ) ) { $id = trim( $parts['path'] ?? '', '/' ); }
		elseif ( in_array( $host, array( 'youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com' ), true ) ) {
			parse_str( $parts['query'] ?? '', $query );
			if ( '/watch' === ( $parts['path'] ?? '' ) ) { $id = is_string( $query['v'] ?? null ) ? $query['v'] : ''; }
			elseif ( preg_match( '~^/(?:embed|shorts|live)/([A-Za-z0-9_-]{11})/?$~', $parts['path'] ?? '', $match ) ) { $id = $match[1]; }
		}
		return preg_match( '/^[A-Za-z0-9_-]{11}$/', $id ) ? $id : '';
	}

	/** Return accessible video markup with no remote image or iframe.
	 * @param string $id Previously validated video ID.
	 * @return string Local player placeholder.
	 */
	public static function video( string $id ): string {
		return '<div class="ttz-video"><span aria-hidden="true" class="ttz-play">▷</span><button type="button" data-ttz-video="' . esc_attr( $id ) . '">' . esc_html__( 'Load YouTube video', 'tuerchenzeit' ) . '</button><p>' . esc_html__( 'Loading the video connects to YouTube. Your IP address is transmitted to the provider.', 'tuerchenzeit' ) . '</p><a class="ttz-video-link" href="https://www.youtube.com/watch?v=' . esc_attr( $id ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Watch on YouTube', 'tuerchenzeit' ) . '</a></div>';
	}

	/** Render a block tree while replacing YouTube embeds before any oEmbed request.
	 * @param array $blocks Parsed WordPress blocks.
	 * @return string Rendered HTML.
	 */
	private static function blocks( array $blocks ): string {
		$html = '';
		foreach ( $blocks as $block ) {
			if ( 'core/shortcode' === $block['blockName'] ) { $html .= self::passages( $block['innerHTML'] ); continue; }
			if ( 'core/embed' === $block['blockName'] ) {
				$id = self::video_id( (string) ( $block['attrs']['url'] ?? '' ) );
				$html .= $id ? self::video( $id ) : '<p>' . esc_html( (string) ( $block['attrs']['url'] ?? '' ) ) . '</p>';
				continue;
			}
			if ( $block['innerBlocks'] ) {
				// Replace nested embeds in serialized child markup, retaining Core layout handling.
				$children = $block['innerBlocks'];
				foreach ( $children as $index => $child ) {
					$rendered = self::blocks( array( $child ) );
					$block['innerBlocks'][ $index ] = array( 'blockName' => 'core/html', 'attrs' => array(), 'innerBlocks' => array(), 'innerHTML' => $rendered, 'innerContent' => array( $rendered ) );
				}
			}
			$html .= render_block( $block );
		}
		return $html;
	}

	/** Execute only the optional local Daily Scripture passage renderer.
	 * @param string $html Classic text or a Shortcode block.
	 * @return string HTML with the supported passage shortcode expanded when available.
	 */
	public static function passages( string $html ): string {
		if ( ! class_exists( '\\Deckerweb\\DailyScripture\\Bible\\Passage' ) || ! shortcode_exists( 'daily_scripture_passage' ) ) { return $html; }
		return preg_replace_callback( '/' . get_shortcode_regex( array( 'daily_scripture_passage' ) ) . '/s', static function ( array $match ): string {
			if ( '[' === $match[1] && ']' === $match[6] ) { return substr( $match[0], 1, -1 ); }
			$input = shortcode_parse_atts( html_entity_decode( $match[3], ENT_QUOTES, 'UTF-8' ) ); $attrs = array();
			foreach ( array( 'translation', 'book', 'chapter', 'from', 'to', 'title', 'layout', 'theme', 'density' ) as $key ) { if ( isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) ) { $attrs[ $key ] = sanitize_text_field( substr( (string) $input[ $key ], 0, 120 ) ); } }
			try { return wp_kses_post( ( new \Deckerweb\DailyScripture\Bible\Passage() )->render( $attrs ) ); } catch ( \Throwable $error ) { return '<p>' . esc_html__( 'The Bible passage could not be loaded.', 'tuerchenzeit' ) . '</p>'; }
		}, $html );
	}

	/** Render original content as safe HTML, with support for Classic URLs and Gutenberg blocks.
	 * @param \WP_Post $post Own door content.
	 * @return string Sanitized, render-ready HTML.
	 */
	public static function render( \WP_Post $post ): string {
		$html = has_blocks( $post->post_content ) ? self::blocks( parse_blocks( $post->post_content ) ) : wpautop( $post->post_content );
		$html = preg_replace_callback( '~<p>\s*(?:<a\b[^>]*>)?(https?://[^<\s]+)(?:</a>)?\s*</p>~i', /** Replace one standalone video URL.
		 * @param array $match Matched paragraph and URL.
		 * @return string Local video markup or unchanged paragraph.
		 */ static function ( array $match ): string {
			$id = self::video_id( $match[1] );
			return $id ? self::video( $id ) : $match[0];
		}, $html );
		return wp_kses_post( self::passages( $html ) );
	}
}
