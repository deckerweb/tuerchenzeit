<?php
/** Explicit administrator-only JSON transfer; never downloads or overwrites originals. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Transfers drafts and assignments with safe local URL remapping. */
final class Transfer {
	/** Register guarded admin actions. @return void */
	public static function hooks(): void { add_action( 'admin_post_ttz_export', array( self::class, 'export_action' ) ); add_action( 'admin_post_ttz_import', array( self::class, 'import_action' ) ); }
	/** Resolve images only when already present in the local media library.
	 * @param mixed $url Exported image URL.
	 * @return int Local attachment ID or zero, without network traffic.
	 */
	public static function local_image( $url ): int {
		if ( ! is_string( $url ) || wp_parse_url( $url, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) { return 0; }
		$id = attachment_url_to_postid( esc_url_raw( $url ) ); return $id && wp_attachment_is_image( $id ) ? $id : 0;
	}
	/** Build a portable schema with originals referenced by URL rather than database ID.
	 * @param int $id Calendar ID.
	 * @return array|\WP_Error Export data or authorization error.
	 */
	public static function data( int $id ): array|\WP_Error {
		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_post', $id ) || Calendar::TYPE !== get_post_type( $id ) ) { return new \WP_Error( 'ttz_transfer', __( 'You are not allowed to transfer this calendar.', 'tuerchenzeit' ) ); }
		$s = Calendar::settings( $id ); unset( $s['next'], $s['switch_date'] );
		$out = array( 'format' => 'tuerchenzeit', 'schema' => 1, 'title' => get_post_field( 'post_title', $id ), 'settings' => $s, 'doors' => array(), 'contents' => array() );
		foreach ( Calendar::doors( $id ) as $day => $door ) {
			if ( $day < 1 || $day > 24 ) { continue; }
			$post = get_post( absint( $door['id'] ?? 0 ) ); $row = array( 'day' => (int) $day, 'mode' => 'link' === ( $door['mode'] ?? '' ) ? 'link' : 'dialog', 'image' => wp_get_attachment_url( absint( $door['image'] ?? 0 ) ) ?: '' );
			if ( $post && Calendar::DOOR === $post->post_type && 'own' === ( $door['source'] ?? '' ) && current_user_can( 'edit_post', $post->ID ) ) {
				$key = 'own-' . $post->ID; $row['source'] = 'own'; $row['content'] = $key;
				$out['contents'][ $key ] = array( 'title' => $post->post_title, 'content' => $post->post_content, 'image' => wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) ) ?: '' );
			} elseif ( $post && 'reference' === ( $door['source'] ?? '' ) && Calendar::resolve( $door ) ) { $row['source'] = 'reference'; $row['url'] = get_permalink( $post ); }
			else { $row['source'] = 'own'; $row['content'] = ''; }
			$out['doors'][] = $row;
		}
		return $out;
	}
	/** Validate JSON completely before creating independent drafts.
	 * @param string $json Uploaded JSON, at most 2 MiB.
	 * @return array|\WP_Error New calendar ID and unresolved references/media count or error.
	 */
	public static function import( string $json ): array|\WP_Error {
		if ( ! current_user_can( 'manage_options' ) ) { return new \WP_Error( 'ttz_transfer', __( 'You are not allowed to transfer this calendar.', 'tuerchenzeit' ) ); }
		$d = self::validate( $json ); if ( is_wp_error( $d ) ) { return $d; }
		$input = $d['settings']; $s = Presentation::sanitize( $input, array() ) + Season::sanitize( $input, array() );
		$s['year'] = is_scalar( $input['year'] ?? null ) ? max( 2000, min( 2199, absint( $input['year'] ) ) ) : (int) wp_date( 'Y' );
		$s['theme'] = in_array( $input['theme'] ?? '', array( 'evergreen', 'village', 'winter', 'bethlehem', 'arrival', 'starpost' ), true ) ? $input['theme'] : 'evergreen';
		$s['color'] = is_string( $input['color'] ?? null ) ? ( sanitize_hex_color( $input['color'] ) ?: '#235b4e' ) : '#235b4e';
		$s['text'] = is_string( $input['text'] ?? null ) ? sanitize_text_field( $input['text'] ) : __( 'The first door opens in', 'tuerchenzeit' );
		$s['countdown'] = ! empty( $input['countdown'] ) ? 1 : 0; $s['locked_notice'] = 'popup' === ( $input['locked_notice'] ?? '' ) ? 'popup' : 'inline';
		$s['protect_references'] = 0; $s['next'] = 0; $s['switch_date'] = '';
		$created = array(); $mapping = array(); $unresolved = 0;
		$id = wp_insert_post( wp_slash( array( 'post_type' => Calendar::TYPE, 'post_status' => 'draft', 'post_title' => sanitize_text_field( $d['title'] ) ) ), true );
		if ( is_wp_error( $id ) ) { return $id; } $created[] = $id;
		foreach ( $d['contents'] as $key => $own ) {
			$new = wp_insert_post( wp_slash( array( 'post_type' => Calendar::DOOR, 'post_status' => 'draft', 'post_title' => sanitize_text_field( $own['title'] ), 'post_content' => wp_kses_post( $own['content'] ) ) ), true );
			if ( is_wp_error( $new ) ) { foreach ( $created as $draft ) { wp_trash_post( $draft ); } return $new; }
			$created[] = $new; $mapping[ $key ] = $new; $image = self::local_image( $own['image'] ?? '' );
			if ( $image ) { set_post_thumbnail( $new, $image ); } elseif ( ! empty( $own['image'] ) ) { ++$unresolved; }
		}
		$doors = array();
		foreach ( $d['doors'] as $row ) {
			$reference = 'reference' === $row['source']; $target = 0;
			if ( $reference ) { $url = esc_url_raw( $row['url'] ); if ( wp_parse_url( $url, PHP_URL_HOST ) === wp_parse_url( home_url(), PHP_URL_HOST ) ) { $target = url_to_postid( $url ); } if ( ! Calendar::resolve( array( 'source' => 'reference', 'id' => $target ) ) ) { $target = 0; ++$unresolved; } }
			else { $target = $mapping[ $row['content'] ] ?? 0; }
			$image = self::local_image( $row['image'] ?? '' ); if ( ! $image && ! empty( $row['image'] ) ) { ++$unresolved; }
			$doors[ $row['day'] ] = array( 'source' => $row['source'], 'id' => $target, 'mode' => 'link' === ( $row['mode'] ?? '' ) ? 'link' : 'dialog', 'image' => $image );
		}
		update_post_meta( $id, '_ttz_settings', $s ); update_post_meta( $id, '_ttz_doors', $doors ); update_post_meta( $id, '_ttz_import_unresolved', $unresolved );
		return array( 'id' => $id, 'unresolved' => $unresolved );
	}
	/** Validate a bounded calendar document without writing data.
	 * @param string $json Calendar JSON.
	 * @return array|\WP_Error Validated data or error.
	 */
	public static function validate( string $json ): array|\WP_Error {
		$bad = new \WP_Error( 'ttz_transfer', __( 'Invalid calendar file. Use a Türchenzeit JSON export (maximum 2 MB).', 'tuerchenzeit' ) );
		if ( strlen( $json ) > 2 * 1024 * 1024 ) { return $bad; }
		$d = json_decode( $json, true, 32 );
		if ( ! is_array( $d ) || 'tuerchenzeit' !== ( $d['format'] ?? '' ) || 1 !== ( $d['schema'] ?? 0 ) || ! is_string( $d['title'] ?? null ) || ! is_array( $d['settings'] ?? null ) || ! is_array( $d['doors'] ?? null ) || ! is_array( $d['contents'] ?? null ) || count( $d['doors'] ) > 24 || count( $d['contents'] ) > 24 ) { return $bad; }
		$days = array();
		foreach ( array_merge( array_values( $d['contents'] ), $d['doors'] ) as $entry ) { if ( ! is_array( $entry ) || ( isset( $entry['image'] ) && ! is_string( $entry['image'] ) ) ) { return $bad; } }
		foreach ( $d['contents'] as $key => $own ) { if ( ! is_string( $key ) || ! is_array( $own ) || ! is_string( $own['title'] ?? null ) || ! is_string( $own['content'] ?? null ) || strlen( $own['content'] ) > 200000 ) { return $bad; } }
		foreach ( $d['doors'] as $row ) {
			if ( ! is_array( $row ) || ! is_int( $row['day'] ?? null ) || $row['day'] < 1 || $row['day'] > 24 || isset( $days[ $row['day'] ] ) || ! in_array( $row['source'] ?? '', array( 'own', 'reference' ), true ) || ( 'own' === $row['source'] && ( ! is_string( $row['content'] ?? null ) || ( '' !== $row['content'] && ! isset( $d['contents'][ $row['content'] ] ) ) ) ) || ( 'reference' === $row['source'] && ! is_string( $row['url'] ?? null ) ) ) { return $bad; }
			$days[ $row['day'] ] = true;
		}
		return $d;
	}

	/** Download an authorized JSON export. @return void */
	public static function export_action(): void {
		$id = absint( $_GET['calendar'] ?? 0 ); check_admin_referer( 'ttz_export_' . $id ); $d = self::data( $id );
		if ( is_wp_error( $d ) ) { wp_die( esc_html( $d->get_error_message() ), '', array( 'response' => 403 ) ); }
		nocache_headers(); header( 'Content-Type: application/json; charset=utf-8' ); header( 'Content-Disposition: attachment; filename="tuerchenzeit-' . $id . '.json"' ); echo wp_json_encode( $d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ); exit;
	}
	/** Read a bounded temporary upload without moving or fetching any media. @return void */
	public static function import_action(): void {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( '', '', array( 'response' => 403 ) ); } check_admin_referer( 'ttz_import' );
		$f = $_FILES['calendar'] ?? array();
		if ( ! is_string( $f['tmp_name'] ?? null ) || ! is_uploaded_file( $f['tmp_name'] ) || ( $f['error'] ?? 1 ) !== UPLOAD_ERR_OK || filesize( $f['tmp_name'] ) > 20 * 1024 * 1024 ) { wp_die( esc_html__( 'Choose a JSON file up to 2 MB or a ZIP up to 20 MB.', 'tuerchenzeit' ) ); }
		$is_zip = 'zip' === strtolower( pathinfo( (string) ( $f['name'] ?? '' ), PATHINFO_EXTENSION ) );
		$result = $is_zip ? Media_Import::run( $f['tmp_name'] ) : self::import( file_get_contents( $f['tmp_name'] ) ); if ( is_wp_error( $result ) ) { wp_die( esc_html( $result->get_error_message() ) ); }
		wp_safe_redirect( get_edit_post_link( $result['id'], 'raw' ) ); exit;
	}
}
