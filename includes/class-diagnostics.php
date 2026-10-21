<?php
/** Native diagnostic information and one explicit editor shortcut. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }
/** Integrates read-only diagnostics and a capability-scoped navigation command. */
final class Diagnostics {
	/** Register native integration hooks. @return void */
	public static function hooks(): void { add_filter( 'debug_information', array( self::class, 'info' ) ); add_action( 'admin_enqueue_scripts', array( self::class, 'command' ) ); }
	/** Append site-local, non-content diagnostic fields.
	 * @param array $info Existing Site Health information.
	 * @return array Information with Türchenzeit section.
	 */
	public static function info( array $info ): array {
		if ( ! current_user_can( 'view_site_health_checks' ) ) { return $info; }
		$fields = array(
			'plugin' => array( 'label' => __( 'Plugin version', 'tuerchenzeit' ), 'value' => VERSION ),
			'editor' => array( 'label' => __( 'Content editor', 'tuerchenzeit' ), 'value' => 'gutenberg' === get_option( 'ttz_editor', 'gutenberg' ) ? __( 'Block Editor (Gutenberg)', 'tuerchenzeit' ) : __( 'Classic editor', 'tuerchenzeit' ) ),
			'timezone' => array( 'label' => __( 'WordPress timezone', 'tuerchenzeit' ), 'value' => wp_timezone_string() ),
			'time' => array( 'label' => __( 'Current website time', 'tuerchenzeit' ), 'value' => wp_date( 'Y-m-d H:i:s P' ) ),
			'json' => array( 'label' => __( 'Available JSON upload', 'tuerchenzeit' ), 'value' => size_format( min( 2 * MB_IN_BYTES, wp_max_upload_size() ) ) ),
			'zip_limit' => array( 'label' => __( 'Available ZIP upload', 'tuerchenzeit' ), 'value' => size_format( min( 20 * MB_IN_BYTES, wp_max_upload_size() ) ) ),
			'zip' => array( 'label' => __( 'PHP ZIP extension', 'tuerchenzeit' ), 'value' => class_exists( '\ZipArchive' ) ? __( 'Available', 'tuerchenzeit' ) : __( 'Unavailable: ZIP import needs this extension. JSON import and calendars remain available.', 'tuerchenzeit' ) ),
		);
		$info['tuerchenzeit'] = array( 'label' => 'Türchenzeit', 'description' => __( 'Calendar diagnostics. Door readiness is shown in the calendar editor.', 'tuerchenzeit' ), 'fields' => $fields ); return $info;
	}
	/** Register only a navigation shortcut for administrators on supported admin screens. @return void */
	public static function command(): void {
		$type = get_post_type_object( Calendar::DOOR );
		if ( is_network_admin() || ! $type || ! current_user_can( 'manage_options' ) || ! current_user_can( $type->cap->create_posts ) ) { return; }
		wp_enqueue_script( 'ttz-command', plugins_url( 'assets/command.js', FILE ), array( 'wp-data', 'wp-commands', 'wp-dom-ready' ), VERSION, true );
		wp_localize_script( 'ttz-command', 'ttzCommand', array( 'label' => __( 'Türchenzeit: create new door content', 'tuerchenzeit' ), 'url' => admin_url( 'post-new.php?post_type=' . Calendar::DOOR ) ) );
	}
}
