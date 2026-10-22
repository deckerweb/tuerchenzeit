<?php
/** GitHub release updates with host identity and requirement validation. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Registers the shared update engine and validates this host's candidates. */
final class Updates {
	/** Attach the updater after translation initialization and validate normalized packages.
	 * @return void
	 */
	public static function hooks(): void {
		add_action( 'init', array( self::class, 'register' ), 20 );
		add_filter( 'upgrader_source_selection', array( self::class, 'validate' ), 30, 4 );
	}

	/** Start this host’s pinned updater alongside other embedded versions.
	 * @return void
	 */
	public static function register(): void {
		$updater_class = require __DIR__ . '/deckerweb-github-release-updater-v2.php';
		if ( ! is_string( $updater_class ) || ! class_exists( $updater_class, false ) || ! defined( $updater_class . '::SUPPORTS_HOST_TRANSLATIONS' ) || ! constant( $updater_class . '::SUPPORTS_HOST_TRANSLATIONS' ) ) {
			add_action( 'admin_notices', array( self::class, 'notice' ) );
			return;
		}
		try {
		$updater = new $updater_class( FILE, 'https://github.com/deckerweb/tuerchenzeit', 'Türchenzeit', Branding::text( 'short' ), array(), array( 'translate' => require __DIR__ . '/updater-translations.php' ) );
		$updater->register();
		} catch ( \InvalidArgumentException $error ) {
			add_action( 'admin_notices', array( self::class, 'notice' ) );
		}
	}

	/** Show compatibility notice only to authorized plugin administrators.
	 * @return void
	 */
	public static function notice(): void {
		$screen = get_current_screen();
		if ( current_user_can( 'update_plugins' ) && $screen && ( 'plugins' === $screen->base || in_array( $screen->post_type, array( Calendar::TYPE, Calendar::DOOR ), true ) ) ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'Türchenzeit requires a newer shared deckerweb Updater. Please update the other deckerweb plugins.', 'tuerchenzeit' ) . '</p></div>';
		}
	}

	/** Reject a mismatched or incompatible update before replacing installed files.
	 * @param string|\WP_Error $source Extracted package directory or previous error.
	 * @param string $remote_source Extraction parent directory.
	 * @param object $upgrader Core upgrader instance.
	 * @param array $extra Update action context.
	 * @return string|\WP_Error Original source on success, otherwise safe failure.
	 */
	public static function validate( $source, string $remote_source, $upgrader, array $extra ) {
		if ( is_wp_error( $source ) || ( $extra['plugin'] ?? '' ) !== plugin_basename( FILE ) ) { return $source; }
		$bulk = $upgrader instanceof \Plugin_Upgrader && ! empty( $upgrader->bulk );
		if ( ( isset( $extra['type'] ) && 'plugin' !== $extra['type'] ) || ( isset( $extra['action'] ) && 'update' !== $extra['action'] ) || ( ! $bulk && ( ( $extra['type'] ?? '' ) !== 'plugin' || ( $extra['action'] ?? '' ) !== 'update' ) ) ) { return $source; }
		global $wp_filesystem, $wp_version;
		$invalid = new \WP_Error( 'ttz_invalid_update', __( 'The update package could not be verified. The installed version has been preserved.', 'tuerchenzeit' ) );
		$main = trailingslashit( $source ) . 'tuerchenzeit.php';
		if ( ! $wp_filesystem || ! $wp_filesystem->exists( $main ) || $wp_filesystem->size( $main ) > 65536 ) { return $invalid; }
		$text = $wp_filesystem->get_contents( $main );
		if ( ! is_string( $text ) ) { return $invalid; }
		$headers = array();
		foreach ( array( 'Plugin Name', 'Text Domain', 'Update URI', 'Version', 'Requires at least', 'Requires PHP' ) as $key ) {
			preg_match( '/^[ \t\/*#@]*' . preg_quote( $key, '/' ) . ':\s*(.+)$/mi', substr( $text, 0, 8192 ), $match );
			$headers[ $key ] = trim( $match[1] ?? '' );
		}
		$offer = get_site_transient( 'update_plugins' )->response[ plugin_basename( FILE ) ] ?? null;
		if ( 'Türchenzeit' !== $headers['Plugin Name'] || 'tuerchenzeit' !== $headers['Text Domain'] || 'https://github.com/deckerweb/tuerchenzeit' !== $headers['Update URI'] || ! $offer || $headers['Version'] !== $offer->new_version || ! version_compare( $headers['Version'], VERSION, '>' ) || ! $headers['Requires at least'] || ! $headers['Requires PHP'] || version_compare( $wp_version, $headers['Requires at least'], '<' ) || version_compare( PHP_VERSION, $headers['Requires PHP'], '<' ) ) { return $invalid; }
		return $source;
	}
}
