<?php
/** Scoped design colors and native door-content patterns. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Preserves theme colors while adding only the relevant Advent palette. */
final class Editor_Extras {
	/** Register editor settings and optional patterns. @return void */
	public static function hooks(): void { add_filter( 'block_editor_settings_all', array( self::class, 'settings' ), 20, 2 ); add_action( 'init', array( self::class, 'patterns' ), 22 ); }
	/** Colors cover the five bundled designs and user accent; no external presets required.
	 * @param string $theme Design key.
	 * @param string $accent User accent.
	 * @return array Gutenberg color descriptors.
	 */
	public static function palette( string $theme, string $accent ): array {
		$colors = array( 'evergreen' => '#163d30', 'village' => '#172e45', 'winter' => '#f6f0e4', 'bethlehem' => '#111f30', 'arrival' => '#eee6d8', 'starpost' => '#f5ebdf' );
		return array( array( 'slug' => 'ttz-background', 'name' => __( 'Türchenzeit · Design background', 'tuerchenzeit' ), 'color' => $colors[ $theme ] ?? $colors['evergreen'] ), array( 'slug' => 'ttz-cream', 'name' => __( 'Türchenzeit · Warm cream', 'tuerchenzeit' ), 'color' => '#fff4df' ), array( 'slug' => 'ttz-forest', 'name' => __( 'Türchenzeit · Forest green', 'tuerchenzeit' ), 'color' => '#233f34' ), array( 'slug' => 'ttz-gold', 'name' => __( 'Türchenzeit · Bethlehem gold', 'tuerchenzeit' ), 'color' => '#d8ba7a' ), array( 'slug' => 'ttz-night', 'name' => __( 'Türchenzeit · Winter night', 'tuerchenzeit' ), 'color' => '#111f30' ), array( 'slug' => 'ttz-accent', 'name' => __( 'Türchenzeit · Accent', 'tuerchenzeit' ), 'color' => sanitize_hex_color( $accent ) ?: '#235b4e' ) );
	}
	/** Find the owning calendar for a landing page or own door; offer all relevant accents when shared.
	 * @param \WP_Post $post Edited post.
	 * @return array Calendar settings indexed by ID.
	 */
	public static function contexts( \WP_Post $post ): array {
		$id = absint( get_post_meta( $post->ID, '_ttz_calendar', true ) );
		if ( 'page' === $post->post_type && 'ttz_landing' === get_page_template_slug( $post->ID ) ) { $id = Calendar::active_id( $id ); return array( $id => Calendar::settings( $id ) ); }
		$out = array();
		if ( Calendar::DOOR === $post->post_type ) {
			foreach ( get_posts( array( 'post_type' => Calendar::TYPE, 'post_status' => array( 'publish', 'draft', 'private', 'future', 'pending' ), 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC', 'suppress_filters' => true ) ) as $cal ) {
				foreach ( Calendar::doors( $cal->ID ) as $d ) { if ( 'own' === ( $d['source'] ?? '' ) && $post->ID === absint( $d['id'] ?? 0 ) ) { $out[ $cal->ID ] = Calendar::settings( $cal->ID ); break; } }
			}
			if ( ! $out ) { $out[0] = Calendar::settings( 0 ); }
		}
		return $out;
	}
	/** Extend both classic editor settings and the modern theme.json editor palette.
	 * @param array $settings Editor settings.
	 * @param \WP_Block_Editor_Context $context Current editor.
	 * @return array Scoped settings retaining theme defaults.
	 */
	public static function settings( array $settings, \WP_Block_Editor_Context $context ): array {
		if ( empty( $context->post ) ) { return $settings; }
		$contexts = self::contexts( $context->post ); if ( ! $contexts ) { return $settings; }
		$palette = array();
		foreach ( $contexts as $id => $s ) {
			foreach ( self::palette( $s['theme'], $s['color'] ) as $color ) {
				$key = $color['slug'];
				if ( isset( $palette[ $key ] ) && $palette[ $key ]['color'] !== $color['color'] ) { $key .= '-' . ltrim( $color['color'], '#' ); $color['slug'] = $key; $color['name'] .= ' · ' . $color['color']; }
				$palette[ $key ] = $color;
			}
		}
		$merge = static function ( array $existing ) use ( $palette ): array { $slugs = array_column( $existing, 'slug' ); foreach ( $palette as $color ) { if ( ! in_array( $color['slug'], $slugs, true ) ) { $existing[] = $color; } } return $existing; };
		$settings['colors'] = $merge( $settings['colors'] ?? array() );
		if ( isset( $settings['__experimentalFeatures'] ) ) { $features = &$settings['__experimentalFeatures']; $features['color']['palette']['custom'] = $merge( $features['color']['palette']['custom'] ?? array() ); }
		return $settings;
	}
	/** Register ten restrained editable content compositions.
	 * Purpose: compose daily stories; tone: calm; layout: one reading column;
	 * typography: small heading and readable paragraphs; colors inherit each design.
	 * @return void
	 */
	public static function patterns(): void {
		$quote = '<!-- wp:quote --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p>' . esc_html__( 'A verse that accompanies you today.', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph --><cite>' . esc_html__( 'Bible reference and translation', 'tuerchenzeit' ) . '</cite></blockquote><!-- /wp:quote -->';
		$paragraph = '<!-- wp:paragraph --><p>' . esc_html__( 'A moment to pause. Add your thoughts for this day here.', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->';
		$items = array(
			'impulse' => array( __( 'Türchenzeit: daily reflection', 'tuerchenzeit' ), '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'A moment of hope', 'tuerchenzeit' ) . '</h2><!-- /wp:heading -->' . $paragraph ),
			'verse' => array( __( 'Türchenzeit: verse and reflection', 'tuerchenzeit' ), $quote . $paragraph ),
			'video' => array( __( 'Türchenzeit: video story', 'tuerchenzeit' ), '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'A story for today', 'tuerchenzeit' ) . '</h2><!-- /wp:heading --><!-- wp:embed {"providerNameSlug":"youtube","responsive":true} /-->' . $paragraph ),
			'image' => array( __( 'Türchenzeit: picture story', 'tuerchenzeit' ), '<!-- wp:image --><figure class="wp-block-image"><img alt=""/></figure><!-- /wp:image -->' . $paragraph ),
			'recipe' => array( __( 'Türchenzeit: Grandma’s recipe book', 'tuerchenzeit' ), '<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained"}} --><div class="wp-block-group">' . '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'A taste of Advent', 'tuerchenzeit' ) . '</h3><!-- /wp:heading -->' . '<!-- wp:paragraph --><p>' . esc_html__( 'A favorite recipe, and the memory that makes it special.', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->' . '<!-- wp:image --><figure class="wp-block-image"><img alt=""/></figure><!-- /wp:image -->' . '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'Ingredients', 'tuerchenzeit' ) . '</h3><!-- /wp:heading -->' . '<!-- wp:list --><ul class="wp-block-list">' . '<!-- wp:list-item --><li>' . esc_html__( 'Add the ingredients and quantities here.', 'tuerchenzeit' ) . '</li><!-- /wp:list-item -->' . '</ul><!-- /wp:list -->' . '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'Preparation', 'tuerchenzeit' ) . '</h3><!-- /wp:heading -->' . '<!-- wp:list {"ordered":true} --><ol class="wp-block-list">' . '<!-- wp:list-item --><li>' . esc_html__( 'Describe the first preparation step.', 'tuerchenzeit' ) . '</li><!-- /wp:list-item -->' . '<!-- wp:list-item --><li>' . esc_html__( 'Add the next steps, baking time and temperature.', 'tuerchenzeit' ) . '</li><!-- /wp:list-item -->' . '</ol><!-- /wp:list -->' . '</div><!-- /wp:group -->' ),
			'kindness' => array( __( 'Türchenzeit: a small act of kindness', 'tuerchenzeit' ), '<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained"}} --><div class="wp-block-group">' . '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'Brighter together', 'tuerchenzeit' ) . '</h3><!-- /wp:heading -->' . '<!-- wp:paragraph --><p>' . esc_html__( 'A small gesture can make someone’s day. What could you give today?', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->' . '<!-- wp:list --><ul class="wp-block-list">' . '<!-- wp:list-item --><li>' . esc_html__( 'Call someone who would enjoy hearing from you.', 'tuerchenzeit' ) . '</li><!-- /wp:list-item -->' . '<!-- wp:list-item --><li>' . esc_html__( 'Offer a neighbor a little practical help.', 'tuerchenzeit' ) . '</li><!-- /wp:list-item -->' . '<!-- wp:list-item --><li>' . esc_html__( 'Write a few kind words to someone.', 'tuerchenzeit' ) . '</li><!-- /wp:list-item -->' . '</ul><!-- /wp:list -->' . '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html__( 'There is no need to do everything. One thoughtful gesture is enough.', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->' . '</div><!-- /wp:group -->' ),
			'letter' => array( __( 'Türchenzeit: an Advent letter', 'tuerchenzeit' ), '<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained"}} --><div class="wp-block-group">' . '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'A few words just for you', 'tuerchenzeit' ) . '</h3><!-- /wp:heading -->' . '<!-- wp:paragraph --><p>' . esc_html__( 'Dear reader,', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->' . '<!-- wp:paragraph --><p>' . esc_html__( 'Today I would like to share a memory, a thank-you, or a little hope with you.', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->' . '<!-- wp:paragraph --><p>' . esc_html__( 'Add your personal message here.', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->' . '<!-- wp:paragraph --><p>' . esc_html__( 'With warm Advent wishes,', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->' . '<!-- wp:paragraph --><p>' . esc_html__( 'Your name', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->' . '</div><!-- /wp:group -->' ),
			'song' => array( __( 'Türchenzeit: a song for today', 'tuerchenzeit' ), '<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained"}} --><div class="wp-block-group">' . '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'A melody for Advent', 'tuerchenzeit' ) . '</h3><!-- /wp:heading -->' . '<!-- wp:paragraph --><p>' . esc_html__( 'Introduce your chosen song and what it means to you.', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->' . '<!-- wp:embed {"providerNameSlug":"youtube","responsive":true} /-->' . '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html__( 'Add your YouTube link in the video block.', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->' . '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html__( 'Song, performer and source', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->' . '<!-- wp:quote --><blockquote class="wp-block-quote">' . '<!-- wp:paragraph --><p>' . esc_html__( 'A line or a thought to carry into the day.', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->' . '</blockquote><!-- /wp:quote -->' . '</div><!-- /wp:group -->' ),
			'craft' => array( __( 'Türchenzeit: creative December hours', 'tuerchenzeit' ), '<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained"}} --><div class="wp-block-group">' . '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'Make something with love', 'tuerchenzeit' ) . '</h3><!-- /wp:heading -->' . '<!-- wp:paragraph --><p>' . esc_html__( 'A simple idea for a quiet December afternoon.', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->' . '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'What you need', 'tuerchenzeit' ) . '</h3><!-- /wp:heading -->' . '<!-- wp:list --><ul class="wp-block-list">' . '<!-- wp:list-item --><li>' . esc_html__( 'List the materials here.', 'tuerchenzeit' ) . '</li><!-- /wp:list-item -->' . '</ul><!-- /wp:list -->' . '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'Step by step', 'tuerchenzeit' ) . '</h3><!-- /wp:heading -->' . '<!-- wp:list {"ordered":true} --><ol class="wp-block-list">' . '<!-- wp:list-item --><li>' . esc_html__( 'Describe the first step.', 'tuerchenzeit' ) . '</li><!-- /wp:list-item -->' . '<!-- wp:list-item --><li>' . esc_html__( 'Explain the next steps in a few clear sentences.', 'tuerchenzeit' ) . '</li><!-- /wp:list-item -->' . '</ol><!-- /wp:list -->' . '<!-- wp:image --><figure class="wp-block-image"><img alt=""/></figure><!-- /wp:image -->' . '<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">' . esc_html__( 'Add a photo of the finished piece in the image block.', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->' . '</div><!-- /wp:group -->' ),
			'prayer' => array( __( 'Türchenzeit: evening prayer and blessing', 'tuerchenzeit' ), '<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained"}} --><div class="wp-block-group">' . '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'A quiet close to the day', 'tuerchenzeit' ) . '</h3><!-- /wp:heading -->' . '<!-- wp:quote --><blockquote class="wp-block-quote">' . '<!-- wp:paragraph --><p>' . esc_html__( 'God, thank you for the light in this day. Be close to those who feel alone. Give us peace for the night and hope for tomorrow. Amen.', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->' . '</blockquote><!-- /wp:quote -->' . '<!-- wp:paragraph --><p>' . esc_html__( 'What am I grateful for today? Whom would I like to remember in prayer?', 'tuerchenzeit' ) . '</p><!-- /wp:paragraph -->' . '</div><!-- /wp:group -->' ),
		);
		foreach ( $items as $slug => $item ) { register_block_pattern( 'tuerchenzeit/content-' . $slug, array( 'title' => $item[0], 'description' => __( 'Editable Advent content made only with standard WordPress blocks.', 'tuerchenzeit' ), 'categories' => array( 'tuerchenzeit' ), 'postTypes' => array( Calendar::DOOR, 'page' ), 'content' => $item[1] ) ); }
	}
}
