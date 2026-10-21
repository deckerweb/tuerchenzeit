<?php
/** Optional, locally supplied typography for Advent content. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Adds two selectable fonts without changing theme defaults or global styles. */
final class Fonts {
	/** Register scoped native editor settings and iframe-compatible styles. @return void */
	public static function hooks(): void {
		add_filter( 'block_editor_settings_all', array( self::class, 'settings' ), 25, 2 );
		add_action( 'enqueue_block_assets', array( self::class, 'editor_assets' ) );
	}
	/** Identify editors belonging to the plugin without reading door contents.
	 * @param \WP_Post|null $post Current edited post.
	 * @return bool Whether optional Advent fonts should be offered.
	 */
	private static function supports( ?\WP_Post $post ): bool {
		return $post && ( Calendar::DOOR === $post->post_type || ( 'page' === $post->post_type && 'ttz_landing' === get_page_template_slug( $post->ID ) ) );
	}
	/** Add native font-family presets while preserving existing families and defaults.
	 * @param array $settings Existing block editor settings.
	 * @param \WP_Block_Editor_Context $context Editor containing the current post.
	 * @return array Settings with two optional custom families in supported editors.
	 */
	public static function settings( array $settings, \WP_Block_Editor_Context $context ): array {
		if ( ! self::supports( $context->post ?? null ) ) { return $settings; }
		$families = &$settings['__experimentalFeatures']['typography']['fontFamilies']['custom'];
		if ( ! is_array( $families ) ) { $families = array(); }
		$slugs = array_column( $families, 'slug' );
		foreach ( array(
			array( 'slug' => 'ttz-source-serif', 'name' => 'Türchenzeit · Source Serif 4', 'fontFamily' => '"TTZ Source Serif 4", Georgia, serif' ),
			array( 'slug' => 'ttz-inter', 'name' => 'Türchenzeit · Inter', 'fontFamily' => '"TTZ Inter", system-ui, sans-serif' ),
		) as $font ) {
			if ( ! in_array( $font['slug'], $slugs, true ) ) { $families[] = $font; }
		}
		return $settings;
	}
	/** Enqueue declarations, never preload font files; browsers fetch only used faces.
	 * @return void
	 */
	public static function enqueue(): void {
		wp_enqueue_style( 'ttz-fonts', plugins_url( 'assets/advent-fonts.css', FILE ), array(), VERSION );
	}
	/** Include the same styles in native editors and their content iframes.
	 * @return void
	 */
	public static function editor_assets(): void {
		if ( is_admin() && self::supports( get_post() ) ) { self::enqueue(); }
	}
}
