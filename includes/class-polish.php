<?php
/** Scoped calendar management presentation and readable assignment columns. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }
/** Adds presentation without replacing native WordPress editing workflows. */
final class Polish {
	/** Register scoped list, editor and documentation hooks. @return void */
	public static function hooks(): void {
		foreach ( array( Calendar::TYPE, Calendar::DOOR ) as $type ) {
			add_filter( 'manage_' . $type . '_posts_columns', array( self::class, 'columns' ) );
			add_action( 'manage_' . $type . '_posts_custom_column', array( self::class, 'cell' ), 10, 2 );
		}
		add_action( 'admin_notices', array( self::class, 'header' ) );
		add_action( 'add_meta_boxes', array( self::class, 'boxes' ), 20 );
		add_action( 'post_submitbox_misc_actions', array( self::class, 'save_status' ) );
	}
	/** Add compact columns while retaining native columns.
	 * @param array $columns Native columns.
	 * @return array Updated columns.
	 */
	public static function columns( array $columns ): array {
		$date = $columns['date'] ?? ''; unset( $columns['date'] );
		if ( Calendar::TYPE === get_current_screen()->post_type ) { $columns['ttz_design'] = __( 'Design', 'tuerchenzeit' ); $columns['ttz_progress'] = __( 'Door readiness', 'tuerchenzeit' ); $columns['ttz_color'] = __( 'Accent color', 'tuerchenzeit' ); }
		else { $columns['ttz_images'] = __( 'Images', 'tuerchenzeit' ); $columns['ttz_assignments'] = __( 'Assigned doors', 'tuerchenzeit' ); }
		$columns['date'] = $date; return $columns;
	}
	/** Index assignments once per management request, including repeated content and draft calendars.
	 * @return array Content IDs mapped to calendar/day/image records.
	 */
	public static function assignments(): array {
		static $index; if ( isset( $index ) ) { return $index; } $index = array();
		$calendars = get_posts( array( 'post_type' => Calendar::TYPE, 'post_status' => array( 'publish', 'draft', 'pending', 'future', 'private' ), 'numberposts' => -1 ) );
		foreach ( $calendars as $calendar ) { $s = Calendar::settings( $calendar->ID ); foreach ( Calendar::doors( $calendar->ID ) as $day => $door ) { if ( 'own' === ( $door['source'] ?? '' ) && ! empty( $door['id'] ) ) { $index[ (int) $door['id'] ][] = array( 'calendar' => $calendar->ID, 'year' => $s['year'], 'day' => $day, 'image' => absint( $door['image'] ?? 0 ) ); } } }
		return $index;
	}
	/** Output one escaped management column.
	 * @param string $column Column key.
	 * @param int $id Post ID.
	 * @return void
	 */
	public static function cell( string $column, int $id ): void {
		if ( 'ttz_design' === $column ) { $s = Calendar::settings( $id ); $theme = isset( self::themes()[ $s['theme'] ] ) ? $s['theme'] : 'evergreen'; echo '<span class="ttz-design-mini ttz-swatch-' . esc_attr( $theme ) . '" aria-hidden="true"><span>1　2　3<br>4　5　6</span></span><span>' . esc_html( self::themes()[ $theme ] ) . '</span>'; }
		if ( 'ttz_progress' === $column ) { $r = Season::readiness( $id ); $ready = 24 - count( $r['missing'] ) - count( $r['unavailable'] ); echo '<strong>' . esc_html( sprintf( /* translators: %d: number of configured doors. */ __( '%d of 24 doors ready', 'tuerchenzeit' ), $ready ) ) . '</strong>'; }
		if ( 'ttz_color' === $column ) { $s = Calendar::settings( $id ); $color = sanitize_hex_color( $s['color'] ) ?: '#235b4e'; echo '<span class="ttz-table-color ttz-color-' . esc_attr( substr( $color, 1 ) ) . '" aria-hidden="true"></span> <code>' . esc_html( $color ) . '</code>'; }
		if ( 'ttz_assignments' === $column ) { $rows = self::assignments()[ $id ] ?? array(); if ( ! $rows ) { echo esc_html__( 'Not assigned', 'tuerchenzeit' ); } foreach ( $rows as $row ) { echo '<a class="ttz-assignment-tag" href="' . esc_url( get_edit_post_link( $row['calendar'], 'raw' ) . '#ttz-doors' ) . '">' . esc_html( get_the_title( $row['calendar'] ) . ' · ' . $row['year'] . ' · ' . sprintf( /* translators: %d: Advent calendar day number. */ __( 'Door %d', 'tuerchenzeit' ), $row['day'] ) ) . '</a>'; } }
		if ( 'ttz_images' === $column ) {
			$featured = get_post_thumbnail_id( $id ); if ( $featured ) { echo '<span class="ttz-table-image">' . wp_get_attachment_image( $featured, 'thumbnail' ) . '<small>' . esc_html__( 'Featured image', 'tuerchenzeit' ) . '</small></span>'; }
			$seen = array(); foreach ( self::assignments()[ $id ] ?? array() as $row ) { if ( $row['image'] && ! isset( $seen[ $row['image'] ] ) ) { $seen[ $row['image'] ] = true; echo '<span class="ttz-table-image">' . wp_get_attachment_image( $row['image'], 'thumbnail' ) . '<small>' . esc_html__( 'Door image', 'tuerchenzeit' ) . '</small></span>'; } }
			if ( ! $featured && ! $seen ) { echo '—'; }
		}
	}
	/** Return translated names for all offered designs. @return array Slugs and names. */
	public static function themes(): array { return array( 'evergreen' => __( 'Evergreen', 'tuerchenzeit' ), 'village' => __( 'Village lights', 'tuerchenzeit' ), 'winter' => __( 'Winter glow', 'tuerchenzeit' ), 'bethlehem' => __( 'Star of Bethlehem', 'tuerchenzeit' ), 'arrival' => __( 'Arrival', 'tuerchenzeit' ), 'starpost' => __( 'Star mail', 'tuerchenzeit' ) ); }
	/** Add a quiet header only to the two management lists. @return void */
	public static function header(): void {
		$s = get_current_screen(); if ( ! $s || 'edit' !== $s->base || ! in_array( $s->post_type, array( Calendar::TYPE, Calendar::DOOR ), true ) ) { return; }
		echo '<div class="ttz-list-header"><span>INSPIRATION · DECKERWEB</span><h2>Türchenzeit</h2><p>' . esc_html( Calendar::TYPE === $s->post_type ? __( 'Your calendars at a glance.', 'tuerchenzeit' ) : __( '24 small moments begin with your contents.', 'tuerchenzeit' ) ) . '</p></div>';
	}
	/** Rename the calendar save box and add own-content assignment guidance. @return void */
	public static function boxes(): void {
		global $wp_meta_boxes;
		if ( isset( $wp_meta_boxes[ Calendar::TYPE ]['side']['core']['submitdiv'] ) ) { $wp_meta_boxes[ Calendar::TYPE ]['side']['core']['submitdiv']['title'] = __( 'Save calendar', 'tuerchenzeit' ); }
		add_meta_box( 'ttz-content-context', __( 'Assigned doors', 'tuerchenzeit' ), array( self::class, 'context' ), Calendar::DOOR, 'side' );
	}
	/** Show context in either supported own-content editor.
	 * @param \WP_Post $post Edited content.
	 * @return void
	 */
	public static function context( \WP_Post $post ): void { echo '<p>' . esc_html__( 'Own door content', 'tuerchenzeit' ) . '</p>'; self::cell( 'ttz_assignments', $post->ID ); }
	/** Add an accessible save-state label to the native submit box. @return void */
	public static function save_status(): void { if ( Calendar::TYPE === get_post_type() ) { echo '<div class="misc-pub-section ttz-save-state" role="status" aria-live="polite">' . esc_html__( 'No unsaved changes.', 'tuerchenzeit' ) . '</div>'; } }
}
