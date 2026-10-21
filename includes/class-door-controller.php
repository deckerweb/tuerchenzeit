<?php
/** Administrator-only REST controller required by the Gutenberg editor. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Keeps Core's editing behavior while closing public published-post reads. */
class Door_Controller extends \WP_REST_Posts_Controller {
	/** Authorize collections for calendar administrators only.
	 * @param \WP_REST_Request $request Incoming collection request.
	 * @return true|\WP_Error Permission result.
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'manage_options' ) ? parent::get_items_permissions_check( $request ) : new \WP_Error( 'ttz_rest_forbidden', __( 'You are not allowed to access door content.', 'tuerchenzeit' ), array( 'status' => rest_authorization_required_code() ) );
	}

	/** Authorize individual reads, including context=view and context=embed.
	 * @param \WP_REST_Request $request Incoming item request.
	 * @return true|\WP_Error Permission result.
	 */
	public function get_item_permissions_check( $request ) {
		return current_user_can( 'manage_options' ) ? parent::get_item_permissions_check( $request ) : new \WP_Error( 'ttz_rest_forbidden', __( 'You are not allowed to access door content.', 'tuerchenzeit' ), array( 'status' => rest_authorization_required_code() ) );
	}
}
