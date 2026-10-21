<?php
/** Opt-in original-post protection using live published calendar assignments. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Applies the earliest opening across protecting calendars without changing post status. */
final class Protection {
	private static ?array $cache = null;
	/** Attach guards to Core queries, singular views, REST, embeds and rendered content. @return void */
	public static function hooks(): void {
		foreach ( array( 'added_post_meta', 'updated_post_meta', 'deleted_post_meta', 'save_post', 'deleted_post', 'switch_blog' ) as $hook ) { add_action( $hook, array( self::class, 'invalidate' ) ); }
		add_action( 'parse_request', array( self::class, 'feed_time' ) );
		add_filter( 'wp_headers', array( self::class, 'headers' ), 10, 2 );
		add_filter( 'comments_clauses', array( self::class, 'comments' ) );
		add_filter( 'xmlrpc_methods', array( self::class, 'xmlrpc' ) );
		add_action( 'pre_get_posts', array( self::class, 'query' ) );
		add_filter( 'posts_where', array( self::class, 'where' ), 20, 2 );
		add_action( 'template_redirect', array( self::class, 'direct' ), 0 );
		add_filter( 'rest_post_dispatch', array( self::class, 'rest_headers' ), 10, 3 );
		add_filter( 'rest_request_before_callbacks', array( self::class, 'rest' ), 10, 3 );
		add_filter( 'the_content', array( self::class, 'content' ), 0 );
		add_filter( 'get_the_excerpt', array( self::class, 'excerpt' ), 0, 2 );
		add_filter( 'oembed_response_data', array( self::class, 'embed' ), 10, 4 );
		add_filter( 'wp_sitemaps_posts_query_args', array( self::class, 'sitemap' ) );
	}
	/** Invalidate only the current request’s derived index after data/context changes. @return void */
	public static function invalidate(): void { self::$cache = null; }
	/** Avoid stale feed 304 responses at a timed opening.
	 * @param \WP $wp Main request object.
	 * @return void
	 */
	public static function feed_time( \WP $wp ): void {
		if ( ! empty( $wp->query_vars['feed'] ) && self::index() ) { unset( $_SERVER['HTTP_IF_NONE_MATCH'], $_SERVER['HTTP_IF_MODIFIED_SINCE'] ); }
	}
	/** Keep timed original URLs and affected feeds/searches uncached within WordPress.
	 * @param array $headers Core response headers.
	 * @param \WP $wp Request environment.
	 * @return array Updated headers.
	 */
	public static function headers( array $headers, \WP $wp ): array {
		$index = self::index(); $id = absint( get_query_var( 'p' ) ?: get_query_var( 'page_id' ) ?: get_queried_object_id() );
		if ( $index && ( isset( $index[ $id ] ) || is_feed() || is_search() ) ) { $headers['Cache-Control'] = 'no-store, private, max-age=0'; unset( $headers['ETag'], $headers['Last-Modified'] ); if ( ! defined( 'DONOTCACHEPAGE' ) ) { define( 'DONOTCACHEPAGE', true ); } }
		return $headers;
	}

	/** Build the live index; no persistent time-sensitive cache can delay an opening.
	 * @return array Original post IDs mapped to earliest Unix opening time.
	 */
	public static function index(): array {
		if ( null !== self::$cache ) { return self::$cache; }
		static $building = false;
		if ( $building ) { return array(); }
		$building = true;
		$calendars = get_posts( array( 'post_type' => Calendar::TYPE, 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC', 'suppress_filters' => true ) );
		$index = array();
		foreach ( $calendars as $calendar ) {
			$s = Calendar::settings( $calendar->ID );
			if ( empty( $s['protect_references'] ) || $calendar->post_password ) { continue; }
			foreach ( Calendar::doors( $calendar->ID ) as $day => $door ) {
				$id = absint( $door['id'] ?? 0 );
				if ( 'reference' !== ( $door['source'] ?? '' ) || ! $id || $day < 1 || $day > 24 ) { continue; }
				$opening = Calendar::opens( (int) $s['year'], (int) $day );
				$index[ $id ] = isset( $index[ $id ] ) ? min( $index[ $id ], $opening ) : $opening;
			}
		}
		$building = false;
		self::$cache = $index;
		return $index;
	}
	/** Determine whether a visitor must not receive an original post.
	 * @param int $id Original post ID.
	 * @param int|null $now Real timestamp or deterministic test boundary.
	 * @return bool Whether locked for this user.
	 */
	public static function locked( int $id, ?int $now = null ): bool {
		if ( current_user_can( 'manage_options' ) && current_user_can( 'edit_post', $id ) ) { return false; }
		$index = self::index();
		return isset( $index[ $id ] ) && ( $now ?? time() ) < $index[ $id ];
	}
	/** Find locked IDs without exposing them to visitors.
	 * @return array Positive integer IDs.
	 */
	public static function locked_ids(): array {
		$ids = array();
		foreach ( self::index() as $id => $opening ) { if ( time() < $opening && ! ( current_user_can( 'manage_options' ) && current_user_can( 'edit_post', $id ) ) ) { $ids[] = (int) $id; } }
		return $ids;
	}
	/** Keep originals out of standard queries even when a caller suppresses SQL filters.
	 * @param \WP_Query $query Mutable Core query.
	 * @return void
	 */
	public static function query( \WP_Query $query ): void {
		if ( Calendar::TYPE === $query->get( 'post_type' ) ) { return; }
		$ids = self::locked_ids(); if ( ! $ids ) { return; }
		$query->set( 'post__not_in', array_unique( array_merge( (array) $query->get( 'post__not_in' ), $ids ) ) );
		$include = $query->get( 'post__in' ); if ( $include ) { $query->set( 'post__in', array_values( array_diff( array_map( 'absint', (array) $include ), $ids ) ) ?: array( 0 ) ); }
	}

	/** Exclude protected posts before pagination/counting, including feeds and Core REST queries.
	 * @param string $where SQL WHERE fragment.
	 * @param \WP_Query $query Query instance.
	 * @return string SQL extended with strictly integer IDs.
	 */
	public static function where( string $where, \WP_Query $query ): string {
		$ids = self::locked_ids();
		if ( $ids ) { global $wpdb; $where .= ' AND ' . $wpdb->posts . '.ID NOT IN (' . implode( ',', array_map( 'absint', $ids ) ) . ')'; }
		return $where;
	}
	/** Deny direct URLs without revealing a title, body or future existence. @return void */
	public static function direct(): void {
		$id = absint( get_query_var( 'p' ) ?: get_query_var( 'page_id' ) ?: get_queried_object_id() );
		// Resolve the original slug without a public query already excluding protected rows.
		if ( ! $id ) { $slug = get_query_var( 'pagename' ) ?: get_query_var( 'name' ); if ( $slug ) { $post = get_page_by_path( $slug, OBJECT, get_post_types( array( 'public' => true ) ) ); $id = $post ? $post->ID : 0; } }
		// Pretty permalinks can already resolve to a 404 after SQL exclusion.
		if ( ! $id && is_404() ) { $id = url_to_postid( home_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ) ); }
		if ( ! $id || ! self::locked( $id ) ) { return; }
		if ( ! defined( 'DONOTCACHEPAGE' ) ) { define( 'DONOTCACHEPAGE', true ); }
		nocache_headers(); header( 'Cache-Control: no-store, private, max-age=0' ); header( 'X-Robots-Tag: noindex, noarchive', true );
		wp_die( esc_html__( 'This content is not available yet.', 'tuerchenzeit' ), esc_html__( 'Content unavailable', 'tuerchenzeit' ), array( 'response' => 404 ) );
	}
	/** Guard Core item routes and subroutes before callbacks run.
	 * @param mixed $response Previous short-circuit response.
	 * @param array $handler Matched handler.
	 * @param \WP_REST_Request $request Incoming request.
	 * @return mixed Prior response or generic 404 error.
	 */
	public static function rest( $response, array $handler, \WP_REST_Request $request ) {
		$id = absint( $request->get_url_params()['parent'] ?? $request->get_url_params()['id'] ?? 0 );
		$route = $request->get_route();
		if ( $id && preg_match( '~^/wp/v2/comments/\d+(?:/|$)~', $route ) ) { $comment = get_comment( $id ); if ( $comment && self::locked( (int) $comment->comment_post_ID ) ) { return new \WP_Error( 'ttz_protected', __( 'Content unavailable', 'tuerchenzeit' ), array( 'status' => 404 ) ); } }
		if ( $id && str_starts_with( $route, '/wp/v2/' ) ) {
			$types = get_post_types( array( 'show_in_rest' => true ), 'objects' );
			foreach ( $types as $type ) {
				$base = $type->rest_base ?: $type->name;
				if ( preg_match( '~^/wp/v2/' . preg_quote( $base, '~' ) . '/\d+(?:/|$)~', $route ) && self::locked( $id ) ) { return new \WP_Error( 'ttz_protected', __( 'Content unavailable', 'tuerchenzeit' ), array( 'status' => 404 ) ); }
			}
		}
		return $response;
	}
	/** Hide comments attached to protected originals from standard public comment queries.
	 * @param array $clauses Comment SQL clauses.
	 * @return array Clauses excluding locked originals.
	 */
	public static function comments( array $clauses ): array {
		$ids = self::locked_ids(); if ( $ids ) { global $wpdb; $clauses['where'] .= ' AND ' . $wpdb->comments . '.comment_post_ID NOT IN (' . implode( ',', array_map( 'absint', $ids ) ) . ')'; } return $clauses;
	}
	/** Filter standard authenticated XML-RPC read results after Core authenticates the caller.
	 * @param array $methods Core XML-RPC method callbacks.
	 * @return array Callbacks with protected originals removed.
	 */
	public static function xmlrpc( array $methods ): array {
		foreach ( array( 'wp.getPost', 'wp.getPosts', 'wp.getPage', 'wp.getPages', 'wp.getPageList', 'wp.getRevisions', 'wp.getComment', 'wp.getComments', 'mt.getRecentPostTitles', 'metaWeblog.getPost', 'metaWeblog.getRecentPosts', 'blogger.getPost', 'blogger.getRecentPosts' ) as $method ) {
			if ( ! isset( $methods[ $method ] ) || ( ! is_callable( $methods[ $method ] ) && ! ( is_string( $methods[ $method ] ) && str_starts_with( $methods[ $method ], 'this:' ) ) ) ) { continue; }
			$callback = $methods[ $method ];
			$methods[ $method ] = static function ( $args ) use ( $callback ) {
				$target = $callback; if ( is_string( $callback ) && str_starts_with( $callback, 'this:' ) ) { global $wp_xmlrpc_server; $target = array( $wp_xmlrpc_server, substr( $callback, 5 ) ); }
				if ( ! is_callable( $target ) ) { return new \IXR_Error( 500, __( 'Content unavailable', 'tuerchenzeit' ) ); }
				$result = call_user_func( $target, $args ); if ( ! is_array( $result ) ) { return $result; }
				$id = absint( $result['post_id'] ?? $result['postid'] ?? $result['page_id'] ?? 0 );
				if ( $id ) { return self::locked( $id ) ? new \IXR_Error( 404, __( 'Content unavailable', 'tuerchenzeit' ) ) : $result; }
				return array_values( array_filter( $result, static fn( $item ) => ! is_array( $item ) || ! self::locked( absint( ( $item['post_parent'] ?? 0 ) ?: ( $item['post_id'] ?? $item['postid'] ?? $item['page_id'] ?? 0 ) ) ) ) );
			};
		}
		return $methods;
	}

	/** Keep Core REST responses out of caches while timed originals are locked.
	 * @param \WP_HTTP_Response $response REST response.
	 * @param \WP_REST_Server $server REST server.
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_HTTP_Response Response with time-safe headers.
	 */
	public static function rest_headers( $response, \WP_REST_Server $server, \WP_REST_Request $request ) {
		if ( self::locked_ids() && str_starts_with( $request->get_route(), '/wp/v2/' ) ) { $response->header( 'Cache-Control', 'no-store, private, max-age=0' ); }
		return $response;
	}

	/** Remove protected content from standard rendering pipelines.
	 * @param string $content Rendered content.
	 * @return string Unchanged or empty.
	 */
	public static function content( string $content ): string { return self::locked( (int) get_the_ID() ) ? '' : $content; }
	/** Remove protected excerpts.
	 * @param string $text Excerpt.
	 * @param \WP_Post $post Original post.
	 * @return string Unchanged or empty.
	 */
	public static function excerpt( string $text, \WP_Post $post ): string { return self::locked( $post->ID ) ? '' : $text; }
	/** Do not expose protected titles or excerpts through oEmbed.
	 * @param array $data Embed data.
	 * @param \WP_Post $post Original post.
	 * @param int $width Requested width.
	 * @param int $height Requested height.
	 * @return array Safe embed response.
	 */
	public static function embed( array $data, \WP_Post $post, int $width, int $height ): array { return self::locked( $post->ID ) ? array() : $data; }
	/** Exclude protected entries from standard sitemap queries.
	 * @param array $args Sitemap query arguments.
	 * @return array Updated arguments.
	 */
	public static function sitemap( array $args ): array { $args['post__not_in'] = array_unique( array_merge( $args['post__not_in'] ?? array(), self::locked_ids() ) ); return $args; }
}
