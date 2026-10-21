<?php
/** Editor choice and strict REST authorization for non-public door content. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Applies site-local editor preferences without making content public. */
final class Editor {
	/** Register the editor switch and REST safeguards. @return void */
	public static function hooks(): void {
		add_filter( 'use_block_editor_for_post_type', array( self::class, 'use_blocks' ), 100, 2 );
		add_filter( 'rest_pre_dispatch', array( self::class, 'guard' ), 10, 3 );
		add_filter( 'rest_post_search_query', array( self::class, 'search_guard' ), 10, 2 );
		add_action( 'init', array( self::class, 'controller' ), 0 );
		add_action( 'rest_api_init', array( self::class, 'controller' ), 0 );
	}

	/** Load the custom controller after Core's REST classes are available. @return void */
	public static function controller(): void { require_once __DIR__ . '/class-door-controller.php'; }

	/** Choose the editor only for our own content type.
	 * @param bool $use Current editor choice.
	 * @param string $type WordPress post type.
	 * @return bool Whether to use Gutenberg.
	 */
	public static function use_blocks( bool $use, string $type ): bool {
		return Calendar::DOOR === $type ? 'gutenberg' === get_option( 'ttz_editor', 'gutenberg' ) : $use;
	}

	/** Reject every door REST subroute for visitors, including embeds, revisions and autosaves.
	 * @param mixed $result Previous pre-dispatch result.
	 * @param \WP_REST_Server $server Active REST server.
	 * @param \WP_REST_Request $request Incoming request.
	 * @return mixed Prior result or an explicit authorization error.
	 */
	public static function guard( $result, \WP_REST_Server $server, \WP_REST_Request $request ) {
		if ( preg_match( '~^/wp/v2/ttz_door(?:/|$)~', $request->get_route() ) && ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'ttz_rest_forbidden', __( 'You are not allowed to access door content.', 'tuerchenzeit' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return $result;
	}

	/** Exclude non-public door titles from the public REST search endpoint.
	 * @param array $args Core post search arguments.
	 * @param \WP_REST_Request $request Current search request.
	 * @return array Safe post search arguments.
	 */
	public static function search_guard( array $args, \WP_REST_Request $request ): array {
		if ( ! current_user_can( 'manage_options' ) ) {
			$types = (array) ( $args['post_type'] ?? array() );
			$types = array_values( array_diff( $types, array( Calendar::DOOR, Calendar::TYPE ) ) );
			$args['post_type'] = $types ?: array( 'ttz_no_public_content' );
		}
		return $args;
	}
}
