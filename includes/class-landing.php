<?php
/** Native Gutenberg landing pages and independent annual editions. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Provides an opt-in standalone page template without changing the active theme. */
final class Landing {
	/** Register page template, scoped assets and administrator actions. @return void */
	public static function hooks(): void {
		add_action( 'init', array( self::class, 'register_template' ), 20 );
		add_action( 'init', array( self::class, 'patterns' ), 21 );
		add_filter( 'block_editor_settings_all', array( self::class, 'editor_styles' ), 10, 2 );
		add_filter( 'theme_page_templates', array( self::class, 'templates' ) );
		add_filter( 'template_include', array( self::class, 'template' ), 99 );
		add_filter( 'the_content', array( self::class, 'trim_footer_tail' ), 30 );
		add_filter( 'body_class', array( self::class, 'body_classes' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'assets' ) );
		add_action( 'admin_post_ttz_landing', array( self::class, 'landing_action' ) );
		add_action( 'admin_post_ttz_edition', array( self::class, 'edition_action' ) );
	}

	/** Register the native block-theme canvas; classic themes use the PHP counterpart.
	 * @return void
	 */
	public static function register_template(): void {
		// Core exposes registered block templates in Site Editor commands. Classic themes use our PHP page template instead.
		if ( ! wp_is_block_theme() ) { return; }
		register_block_template( 'tuerchenzeit//ttz_landing', array(
			'title' => __( 'Türchenzeit standalone landing page', 'tuerchenzeit' ),
			'post_types' => array( 'page' ),
			'content' => '<!-- wp:group {"tagName":"main","layout":{"type":"default"}} --><main class="wp-block-group"><!-- wp:post-content /--></main><!-- /wp:group -->',
		) );
	}

	/** Register optional full-width core-block sections, never inserted automatically.
	 * Design: optional brand framing; quiet and warm; full-width band around constrained
	 * content; restrained site-title hierarchy; local cream/forest color fallbacks editable
	 * through native block color controls. No theme-specific presets are assumed.
	 * @return void
	 */
	public static function patterns(): void {
		register_block_pattern_category( 'tuerchenzeit', array( 'label' => 'Türchenzeit' ) );
		foreach ( array( 'header' => __( 'Türchenzeit: full-width header', 'tuerchenzeit' ), 'footer' => __( 'Türchenzeit: full-width footer', 'tuerchenzeit' ) ) as $tag => $title ) {
			register_block_pattern( 'tuerchenzeit/' . $tag, array( 'title' => $title, 'categories' => array( 'tuerchenzeit' ), 'postTypes' => array( 'page' ), 'viewportWidth' => 1200, 'description' => __( 'Optional full-width band. Replace the existing section and edit colors and text with the standard block controls.', 'tuerchenzeit' ), 'content' => self::pattern_markup( $tag ) ) );
		}
	}

	/** Generate a native section with editable background, spacing and text.
	 * @param string $tag Header or footer section type.
	 * @return string Serialized core group and paragraph blocks.
	 */
	public static function pattern_markup( string $tag ): string {
		$header = 'header' === $tag; $tag = $header ? 'header' : 'footer';
		$attrs = array( 'tagName' => $tag, 'align' => 'full', 'style' => array( 'color' => array( 'background' => '#233f34', 'text' => '#fff4df' ), 'spacing' => array( 'padding' => array( 'top' => '32px', 'bottom' => '32px', 'left' => '24px', 'right' => '24px' ), 'margin' => array( 'top' => '0', 'bottom' => '0' ) ) ), 'layout' => array( 'type' => 'constrained', 'contentSize' => '1200px' ) );
		$inner = $header ? '<!-- wp:site-title {"level":0,"textAlign":"center"} /-->' : '';
		$text = $header ? Branding::text( 'slogan' ) : __( 'Made with love for Advent.', 'tuerchenzeit' );
		$inner .= '<!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">' . esc_html( $text ) . '</p><!-- /wp:paragraph -->';
		return '<!-- wp:group ' . wp_json_encode( $attrs ) . ' --><' . $tag . ' class="wp-block-group alignfull has-text-color has-background" style="color:#fff4df;background-color:#233f34;margin-top:0;margin-bottom:0;padding-top:32px;padding-right:24px;padding-bottom:32px;padding-left:24px">' . $inner . '</' . $tag . '><!-- /wp:group -->';
	}

	/** Match the standalone page layout inside its own block editor only.
	 * @param array $settings Existing editor settings.
	 * @param \WP_Block_Editor_Context $context Current editor context.
	 * @return array Editor settings with scoped canvas styles.
	 */
	public static function editor_styles( array $settings, \WP_Block_Editor_Context $context ): array {
		if ( ! empty( $context->post ) && 'ttz_landing' === get_page_template_slug( $context->post->ID ) ) {
			$id = Calendar::active_id( absint( get_post_meta( $context->post->ID, '_ttz_calendar', true ) ) );
			$s = Calendar::settings( $id );
			$colors = array( 'evergreen' => array( '#eeeee5', '#273f34' ), 'village' => array( '#111f30', '#f4e8cf' ), 'bethlehem' => array( '#111f30', '#f4e8cf' ), 'arrival' => array( '#eee6d8', '#584833' ), 'starpost' => array( '#f5ebdf', '#543746' ), 'winter' => array( '#faf7f0', '#3f433d' ) );
			$palette = $colors[ $s['theme'] ] ?? $colors['evergreen'];
			$css = str_replace( array( '#eeeee5', '#273f34' ), $palette, file_get_contents( dirname( FILE ) . '/assets/landing-editor.css' ) );
			$settings['styles'][] = array( 'css' => $css );
		}
		return $settings;
	}

	/** Add the dedicated template to the normal page editor.
	 * @param array $templates Existing template choices.
	 * @return array Updated template choices.
	 */
	public static function templates( array $templates ): array {
		$templates['ttz_landing'] = __( 'Türchenzeit standalone landing page', 'tuerchenzeit' );
		return $templates;
	}

	/** Resolve only explicitly selected standalone pages to the plugin template.
	 * @param string $template Theme template path.
	 * @return string Selected template path.
	 */
	public static function template( string $template ): string {
		return is_page() && ! wp_is_block_theme() && 'ttz_landing' === get_page_template_slug() ? dirname( FILE ) . '/templates/landing.php' : $template;
	}

	/** Ignore empty editor tail paragraphs after a full-width footer on its own page.
	 * Stored blocks remain intact; meaningful paragraphs and other page types are untouched.
	 * @param string $content Rendered post content.
	 * @return string Content without empty paragraphs following the final full-width footer.
	 */
	public static function trim_footer_tail( string $content ): string {
		if ( ! is_page() || 'ttz_landing' !== get_page_template_slug() || get_the_ID() !== get_queried_object_id() ) { return $content; }
		$blocks = parse_blocks( get_post_field( 'post_content', get_queried_object_id() ) );
		while ( $blocks ) {
			$last = array_pop( $blocks );
			if ( in_array( $last['blockName'], array( null, 'core/paragraph' ), true ) && '' === trim( html_entity_decode( wp_strip_all_tags( $last['innerHTML'] ), ENT_QUOTES, 'UTF-8' ), " \t\n\r\0\x0B\xc2\xa0" ) ) { continue; }
			if ( 'core/group' === $last['blockName'] && 'full' === ( $last['attrs']['align'] ?? '' ) ) { return preg_replace( '~(?:\s*<p>(?:\s|&nbsp;|<br\s*/?>)*</p>)+\s*$~', '', $content ); }
			break;
		}
		return $content;
	}

	/** Derive landing colors from its current live calendar edition.
	 * @param array $classes Existing body classes.
	 * @return array Body classes with a strictly validated theme suffix.
	 */
	public static function body_classes( array $classes ): array {
		if ( is_page() && 'ttz_landing' === get_page_template_slug() ) {
			$id = Calendar::active_id( absint( get_post_meta( get_queried_object_id(), '_ttz_calendar', true ) ) );
			$s = Calendar::settings( $id );
			$theme = in_array( $s['theme'], array( 'evergreen', 'village', 'winter', 'bethlehem', 'arrival', 'starpost' ), true ) ? $s['theme'] : 'evergreen';
			$classes[] = 'ttz-landing'; $classes[] = 'ttz-site-' . $theme;
		}
		return $classes;
	}

	/** Load standalone assets only for explicitly selected pages. @return void */
	public static function assets(): void {
		if ( is_page() && 'ttz_landing' === get_page_template_slug() ) {
			Frontend::assets();
			wp_enqueue_style( 'ttz-landing', plugins_url( 'assets/landing.css', FILE ), array( 'tuerchenzeit' ), VERSION );
		}
	}

	/** Generate editable native blocks: brand header, hero, shortcode, and legal footer.
	 * Design: welcoming seasonal purpose; restrained tone; centered composition;
	 * serif display hierarchy supplied by the page template; colors inherited from the selected theme.
	 * @param int $id Calendar ID to embed.
	 * @return string Valid core block markup, without custom pattern CSS or scripts.
	 */
	public static function markup( int $id ): string {
		$title = esc_html( get_the_title( $id ) );
		$welcome = esc_html( Branding::text( 'slogan' ) );
		$intro = esc_html__( 'Take a moment to pause. A new surprise awaits you here every day in December.', 'tuerchenzeit' );
		$s = Calendar::settings( $id );
		if ( 'arrival' === $s['theme'] ) { $intro = esc_html__( 'Advent means arrival. We await Jesus Christ, our Savior.', 'tuerchenzeit' ); }
		elseif ( 'bethlehem' === $s['theme'] ) { $intro = esc_html__( 'Expecting Jesus Christ. Following the light of hope.', 'tuerchenzeit' ); }
		$footer = esc_html__( 'Made with love for Advent.', 'tuerchenzeit' );
		$privacy = get_privacy_policy_url();
		if ( $privacy ) { $footer .= ' · <a href="' . esc_url( $privacy ) . '">' . esc_html__( 'Privacy policy', 'tuerchenzeit' ) . '</a>'; }
		return '<!-- wp:group {"tagName":"header","layout":{"type":"constrained"}} --><header class="wp-block-group"><!-- wp:site-logo {"width":64,"align":"center"} /--><!-- wp:site-title {"level":0,"textAlign":"center"} /--></header><!-- /wp:group -->' .
			'<!-- wp:group {"tagName":"section","layout":{"type":"constrained"}} --><section class="wp-block-group"><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">' . $welcome . '</p><!-- /wp:paragraph --><!-- wp:heading {"textAlign":"center","level":1} --><h1 class="wp-block-heading has-text-align-center">' . $title . '</h1><!-- /wp:heading --><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">' . $intro . '</p><!-- /wp:paragraph --></section><!-- /wp:group -->' .
			'<!-- wp:shortcode -->[tuerchenzeit id="' . $id . '" heading="0"]<!-- /wp:shortcode -->' .
			'<!-- wp:group {"tagName":"footer","layout":{"type":"constrained"}} --><footer class="wp-block-group"><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">' . $footer . '</p><!-- /wp:paragraph --></footer><!-- /wp:group -->';
	}

	/** Create one draft landing page, reusing its existing valid page on repeated clicks.
	 * @param int $id Authorized calendar ID.
	 * @return int|\WP_Error Created or existing page ID, otherwise error.
	 */
	public static function create_page( int $id ): int|\WP_Error {
		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_post', $id ) || ! Calendar::get( $id, true ) ) { return new \WP_Error( 'ttz_forbidden', __( 'Action not allowed.', 'tuerchenzeit' ) ); }
		$existing = get_post( absint( get_post_meta( $id, '_ttz_landing_page', true ) ) );
		if ( $existing && 'page' === $existing->post_type && 'trash' !== $existing->post_status ) { return $existing->ID; }
		$page = wp_insert_post( array( 'post_type' => 'page', 'post_title' => get_the_title( $id ), 'post_status' => 'draft', 'post_content' => wp_slash( self::markup( $id ) ), 'meta_input' => array( '_wp_page_template' => 'ttz_landing', '_ttz_calendar' => $id ) ), true );
		if ( ! is_wp_error( $page ) ) { update_post_meta( $id, '_ttz_landing_page', $page ); }
		return $page;
	}

	/** Create the next annual draft, copying own contents so edits cannot change the live edition.
	 * @param int $id Authorized source calendar ID.
	 * @return int|\WP_Error New or already prepared calendar ID.
	 */
	public static function create_edition( int $id ): int|\WP_Error {
		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_post', $id ) || ! Calendar::get( $id, true ) ) { return new \WP_Error( 'ttz_forbidden', __( 'Action not allowed.', 'tuerchenzeit' ) ); }
		$s = Calendar::settings( $id );
		$existing = get_post( absint( $s['next'] ) );
		if ( $existing && Calendar::TYPE === $existing->post_type && 'trash' !== $existing->post_status ) { return $existing->ID; }
		if ( (int) $s['year'] >= 2199 ) { return new \WP_Error( 'ttz_year', __( 'The maximum calendar year has been reached.', 'tuerchenzeit' ) ); }
		$next_settings = $s;
		++$next_settings['year']; $next_settings['next'] = 0; $next_settings['switch_date'] = '';
		$next = wp_insert_post( array( 'post_type' => Calendar::TYPE, 'post_title' => get_the_title( $id ) . ' ' . $next_settings['year'], 'post_status' => 'draft' ), true );
		if ( is_wp_error( $next ) ) { return $next; }
		$doors = Calendar::doors( $id ); $copied = array();
		foreach ( $doors as &$door ) {
			if ( 'own' !== ( $door['source'] ?? '' ) || empty( $door['id'] ) ) { continue; }
			$source = get_post( $door['id'] );
			if ( ! $source || Calendar::DOOR !== $source->post_type ) { continue; }
			if ( ! isset( $copied[ $source->ID ] ) ) {
				$copy = wp_insert_post( array( 'post_type' => Calendar::DOOR, 'post_title' => $source->post_title, 'post_content' => wp_slash( $source->post_content ), 'post_excerpt' => wp_slash( $source->post_excerpt ), 'post_status' => 'draft' ), true );
				if ( is_wp_error( $copy ) ) { wp_trash_post( $next ); return $copy; }
				if ( get_post_thumbnail_id( $source ) ) { set_post_thumbnail( $copy, get_post_thumbnail_id( $source ) ); }
				$copied[ $source->ID ] = $copy;
			}
			$door['id'] = $copied[ $source->ID ];
		}
		unset( $door );
		update_post_meta( $next, '_ttz_settings', $next_settings ); update_post_meta( $next, '_ttz_doors', $doors );
		$s['next'] = $next; $s['switch_date'] = $next_settings['year'] . '-11-01T00:00';
		update_post_meta( $id, '_ttz_settings', $s );
		return $next;
	}

	/** Handle the explicitly requested landing-page draft creation. @return void */
	public static function landing_action(): void { self::action( 'landing' ); }

	/** Handle the explicitly requested next-edition draft creation. @return void */
	public static function edition_action(): void { self::action( 'edition' ); }

	/** Validate capabilities and nonce, then redirect to the newly prepared editor.
	 * @param string $kind Internal action kind, landing or edition.
	 * @return void
	 */
	private static function action( string $kind ): void {
		$id = isset( $_GET['calendar'] ) && is_scalar( $_GET['calendar'] ) ? absint( $_GET['calendar'] ) : 0;
		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_post', $id ) ) { wp_die( esc_html__( 'Action not allowed.', 'tuerchenzeit' ), '', array( 'response' => 403 ) ); }
		check_admin_referer( 'ttz_' . $kind . '_' . $id );
		$result = 'landing' === $kind ? self::create_page( $id ) : self::create_edition( $id );
		if ( is_wp_error( $result ) ) { wp_die( esc_html( $result->get_error_message() ) ); }
		wp_safe_redirect( get_edit_post_link( $result, 'raw' ) ); exit;
	}
}
