<?php
/** Bounded ZIP imports with validated raster images and no archive extraction. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Imports portable calendar.json packages into independent drafts and local media. */
final class Media_Import {
	/** Import an administrator-provided ZIP. Files are never extracted by archive paths.
	 * @param string $path Temporary uploaded archive path.
	 * @return array|\WP_Error Import result or actionable error; new media are removed on failure.
	 */
	public static function run( string $path ): array|\WP_Error {
		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'upload_files' ) ) { return new \WP_Error( 'ttz_zip', __( 'You are not allowed to import images.', 'tuerchenzeit' ) ); }
		$bad = new \WP_Error( 'ttz_zip', __( 'Invalid ZIP. Include calendar.json and JPEG, PNG, WebP or GIF images in media/. Maximum: 20 MB compressed, 50 MB unpacked, 100 images, 5 MB per image. SVG is not supported.', 'tuerchenzeit' ) );
		if ( ! class_exists( '\ZipArchive' ) ) { return new \WP_Error( 'ttz_zip', __( 'ZIP import requires the PHP ZIP extension on your server.', 'tuerchenzeit' ) ); }
		if ( ! is_file( $path ) || filesize( $path ) > 20 * MB_IN_BYTES ) { return $bad; }
		$zip = new \ZipArchive(); if ( true !== $zip->open( $path, \ZipArchive::RDONLY ) ) { return $bad; }
		$created = array();
		try {
			$files = array(); $total = 0;
			if ( $zip->numFiles > 102 ) { return $bad; }
			for ( $i = 0; $i < $zip->numFiles; ++$i ) {
				$s = $zip->statIndex( $i ); if ( ! $s ) { return $bad; } $name = $s['name'];
				if ( isset( $files[ strtolower( $name ) ] ) || ! preg_match( '~^(calendar\.json|media/|media/[a-zA-Z0-9][a-zA-Z0-9._-]*\.(?:jpg|jpeg|png|webp|gif))$~D', $name ) || ! empty( $s['encryption_method'] ) ) { return $bad; }
				$opsys = 0; $attrs = 0; $zip->getExternalAttributesIndex( $i, $opsys, $attrs );
				if ( 3 === $opsys && ( ( $attrs >> 16 ) & 0170000 ) === 0120000 ) { return $bad; }
				$total += $s['size']; if ( $total > 50 * MB_IN_BYTES || $s['size'] > ( 'calendar.json' === $name ? 2 : 5 ) * MB_IN_BYTES ) { return $bad; }
				$files[ strtolower( $name ) ] = $s;
			}
			if ( ! isset( $files['calendar.json'] ) ) { return $bad; }
			$json = $zip->getFromName( 'calendar.json', 2 * MB_IN_BYTES + 1 ); if ( ! is_string( $json ) ) { return $bad; }
			$valid = Transfer::validate( $json ); if ( is_wp_error( $valid ) ) { return $valid; } $data = $valid;
			$manifest = $data['media'] ?? array(); if ( ! is_array( $manifest ) || count( $manifest ) > 100 ) { return $bad; }
			$aliases = array(); $alts = array();
			foreach ( $manifest as $item ) {
				if ( ! is_array( $item ) || ! is_string( $item['file'] ?? null ) || ! isset( $files[ strtolower( $item['file'] ) ] ) || ! str_starts_with( $item['file'], 'media/' ) || ! is_string( $item['url'] ?? '' ) || ! is_string( $item['alt'] ?? '' ) ) { return $bad; }
				if ( ! empty( $item['url'] ) ) { if ( ! preg_match( '~^https?://~i', $item['url'] ) || ! wp_parse_url( $item['url'], PHP_URL_HOST ) || isset( $aliases[ $item['url'] ] ) ) { return $bad; } $aliases[ $item['url'] ] = $item['file']; }
				$alts[ $item['file'] ] = sanitize_text_field( $item['alt'] ?? '' );
			}
			require_once ABSPATH . 'wp-admin/includes/file.php'; require_once ABSPATH . 'wp-admin/includes/media.php'; require_once ABSPATH . 'wp-admin/includes/image.php';
			$images = array();
			// Validate every image before any persistent write; cap decoded pixel dimensions as well.
			foreach ( $files as $s ) {
				$name = $s['name']; if ( 'calendar.json' === $name || 'media/' === $name ) { continue; }
				$bytes = $zip->getFromName( $name, 5 * MB_IN_BYTES + 1 ); if ( ! is_string( $bytes ) || strlen( $bytes ) !== $s['size'] || count( $images ) >= 100 ) { return $bad; }
				$info = @getimagesizefromstring( $bytes ); $ext = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
				$types = array( 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif' );
				if ( ! $info || ( $info['mime'] ?? '' ) !== $types[ $ext ] || $info[0] * $info[1] > 20000000 || $info[0] > 10000 || $info[1] > 10000 ) { return $bad; }
				$images[ $name ] = true;
			}
			$urls = array(); $ids = array();
			foreach ( $images as $name => $_ ) {
				$tmp = wp_tempnam( basename( $name ) ); if ( ! $tmp || false === file_put_contents( $tmp, $zip->getFromName( $name, 5 * MB_IN_BYTES + 1 ) ) ) { if ( $tmp ) { wp_delete_file( $tmp ); } return $bad; }
				try { $id = media_handle_sideload( array( 'name' => basename( $name ), 'tmp_name' => $tmp ), 0 ); } finally { if ( is_file( $tmp ) ) { wp_delete_file( $tmp ); } }
				if ( is_wp_error( $id ) ) { return $id; } $created[] = $id;
				update_post_meta( $id, '_wp_attachment_image_alt', $alts[ $name ] ?? '' ); $urls[ $name ] = wp_get_attachment_url( $id ); $ids[ $name ] = $id;
			}
			foreach ( $aliases as $old => $name ) { if ( ! isset( $urls[ $name ] ) ) { return $bad; } $urls[ $old ] = $urls[ $name ]; $ids[ $old ] = $ids[ $name ]; }
			foreach ( $data['contents'] as &$own ) {
				$own['content'] = self::blocks( strtr( $own['content'], $urls ), $ids, $urls );
				$own['image'] = $urls[ $own['image'] ?? '' ] ?? ( $own['image'] ?? '' );
			} unset( $own );
			foreach ( $data['doors'] as &$door ) { $door['image'] = $urls[ $door['image'] ?? '' ] ?? ( $door['image'] ?? '' ); } unset( $door ); unset( $data['media'] );
			$result = Transfer::import( wp_json_encode( $data ) );
			if ( ! is_wp_error( $result ) ) { $result['images'] = count( $created ); $created = array(); }
			return $result;
		} finally { $zip->close(); foreach ( $created as $id ) { wp_delete_attachment( $id, true ); } }
	}
	/** Remap core image IDs and HTML media classes while retaining native block content.
	 * @param string $content URL-remapped HTML or serialized blocks.
	 * @param array $ids Original references mapped to attachment IDs.
	 * @param array $urls Original references mapped to local URLs.
	 * @return string Updated native content.
	 */
	private static function blocks( string $content, array $ids, array $urls ): string {
		$by_url = array(); foreach ( $ids as $key => $id ) { $by_url[ $urls[ $key ] ] = $id; }
		$tags = new \WP_HTML_Tag_Processor( $content );
		while ( $tags->next_tag( 'IMG' ) ) {
			$url = $tags->get_attribute( 'src' );
			if ( is_string( $url ) && isset( $by_url[ $url ] ) ) {
				$classes = $tags->get_attribute( 'class' );
				if ( is_string( $classes ) ) { $tags->set_attribute( 'class', preg_replace( '/\bwp-image-\d+\b/', 'wp-image-' . $by_url[ $url ], $classes ) ); }
				$tags->add_class( 'wp-image-' . $by_url[ $url ] );
			}
		}
		$content = $tags->get_updated_html();
		$walk = static function ( array $blocks ) use ( &$walk, $by_url ): array {
			foreach ( $blocks as &$b ) {
				if ( 'core/image' === $b['blockName'] ) {
					unset( $b['attrs']['id'] );
					if ( preg_match( '/<img[^>]+src=["\x27]([^"\x27]+)/', $b['innerHTML'], $m ) && isset( $by_url[ html_entity_decode( $m[1], ENT_QUOTES ) ] ) ) {
						$id = $by_url[ html_entity_decode( $m[1], ENT_QUOTES ) ]; $b['attrs']['id'] = $id;
						foreach ( $b['innerContent'] as &$part ) { if ( is_string( $part ) ) { $part = preg_replace( '/\bwp-image-\d+\b/', 'wp-image-' . $id, $part ); } } unset( $part );
					}
				}
				$b['innerBlocks'] = $walk( $b['innerBlocks'] );
			} unset( $b ); return $blocks;
		};
		return has_blocks( $content ) ? serialize_blocks( $walk( parse_blocks( $content ) ) ) : $content;
	}
}
