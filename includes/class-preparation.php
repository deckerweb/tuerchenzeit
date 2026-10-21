<?php
/** Small, scoped preparation helpers for the existing calendar editor. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }
/** Offers existing-page navigation and a per-user preparation reminder. */
final class Preparation {
	/** Register the authenticated preference endpoint. @return void */
	public static function hooks(): void { add_action( 'wp_ajax_ttz_checklist', array( self::class, 'preference' ) ); }
	/** Resolve an existing linked page without creating or changing anything.
	 * @param int $calendar Calendar ID in the current website.
	 * @return \WP_Post|null Existing, non-trashed page or null.
	 */
	public static function page( int $calendar ): ?\WP_Post {
		$id = absint( get_post_meta( $calendar, '_ttz_landing_page', true ) );
		if ( ! $id ) { return null; }
		$page = get_post( $id );
		return $page && 'page' === $page->post_type && 'trash' !== $page->post_status ? $page : null;
	}
	/** Show safe navigation for an existing page, or the existing preparation action.
	 * @param \WP_Post $calendar Saved calendar being edited.
	 * @return void
	 */
	public static function website_actions( \WP_Post $calendar ): void {
		$page = self::page( $calendar->ID );
		if ( $page ) {
			if ( current_user_can( 'edit_post', $page->ID ) ) {
				echo '<a class="button" href="' . esc_url( get_edit_post_link( $page->ID, 'raw' ) ) . '">' . esc_html__( 'Edit Advent website', 'tuerchenzeit' ) . '</a> ';
				$published = 'publish' === $page->post_status;
				echo '<a class="button" target="_blank" rel="noopener noreferrer" href="' . esc_url( $published ? get_permalink( $page ) : get_preview_post_link( $page ) ) . '">' . esc_html( $published ? __( 'View Advent website', 'tuerchenzeit' ) : __( 'Preview Advent website', 'tuerchenzeit' ) ) . '</a> ';
				if ( ! $published ) { echo '<span class="description">' . esc_html__( 'Draft — not public yet.', 'tuerchenzeit' ) . '</span> '; }
			} else { echo esc_html__( 'This Advent website is already assigned.', 'tuerchenzeit' ) . ' '; }
			return;
		}
		echo '<a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ttz_landing&calendar=' . $calendar->ID ), 'ttz_landing_' . $calendar->ID ) ) . '">' . esc_html__( 'Prepare landing page', 'tuerchenzeit' ) . '</a> ';
	}
	/** Render an optional reminder, without claiming a completed manual review.
	 * @param \WP_Post $calendar Saved calendar being edited.
	 * @return void
	 */
	public static function checklist( \WP_Post $calendar ): void {
		if ( 'auto-draft' === $calendar->post_status ) { return; }
		$hidden = (bool) get_user_meta( get_current_user_id(), self::key( $calendar->ID ), true );
		?><div class="ttz-checklist-wrap" data-calendar="<?php echo esc_attr( $calendar->ID ); ?>"><button type="button" class="button-link" data-show-checklist <?php echo $hidden ? '' : 'hidden'; ?>><?php esc_html_e( 'Show preparation checklist', 'tuerchenzeit' ); ?></button><section class="ttz-start-checklist" <?php echo $hidden ? 'hidden' : ''; ?>><div class="ttz-checklist-heading"><h3><?php esc_html_e( 'Preparation checklist', 'tuerchenzeit' ); ?></h3><button type="button" class="button-link" data-hide-checklist><?php esc_html_e( 'Hide checklist', 'tuerchenzeit' ); ?></button></div><ol role="list"><li><span class="ttz-checklist-number" aria-hidden="true">1</span><?php esc_html_e( 'Assign all 24 doors and publish their contents.', 'tuerchenzeit' ); ?></li><li><span class="ttz-checklist-number" aria-hidden="true">2</span><a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ttz_preview&calendar=' . $calendar->ID ), 'ttz_preview_page_' . $calendar->ID ) ); ?>"><?php esc_html_e( 'Open the date preview and check your doors.', 'tuerchenzeit' ); ?></a></li><li><span class="ttz-checklist-number" aria-hidden="true">3</span><?php echo esc_html( self::page( $calendar->ID ) ? __( 'Publish the calendar and your Advent website.', 'tuerchenzeit' ) : __( 'Publish the calendar and insert it into your page.', 'tuerchenzeit' ) ); ?></li></ol><p class="description"><?php esc_html_e( 'This checklist is a reminder, not an automatic publication check. Its visibility is saved for your user and this calendar.', 'tuerchenzeit' ); ?></p></section><span class="ttz-checklist-status" role="status"></span></div><?php
	}
	/** Scope user preferences by website as calendar IDs may repeat across a network.
	 * @param int $calendar Current website calendar ID.
	 * @return string Site- and object-scoped metadata key.
	 */
	private static function key( int $calendar ): string { return '_ttz_checklist_' . get_current_blog_id() . '_' . $calendar; }
	/** Save only the current user's checklist visibility after nonce and object authorization.
	 * @return void Sends a bounded JSON response and ends execution.
	 */
	public static function preference(): void {
		if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) && is_string( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '' ) || ! current_user_can( 'manage_options' ) ) { wp_send_json_error( array(), 403 ); }
		check_ajax_referer( 'ttz_checklist', 'nonce' );
		$id = absint( $_POST['calendar'] ?? 0 );
		if ( ! Calendar::get( $id, true ) || ! current_user_can( 'edit_post', $id ) || ! in_array( $_POST['hidden'] ?? '', array( '0', '1' ), true ) ) { wp_send_json_error( array(), 403 ); }
		if ( '1' === $_POST['hidden'] ) { update_user_meta( get_current_user_id(), self::key( $id ), 1 ); }
		else { delete_user_meta( get_current_user_id(), self::key( $id ) ); }
		wp_send_json_success( array( 'hidden' => '1' === $_POST['hidden'] ) );
	}
}
