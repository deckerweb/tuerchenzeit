<?php
/** Translate shared updater messages through the host text domain. */
defined( 'ABSPATH' ) || exit;
/** Translate a shared message.
 * @param string $message English source message.
 * @return string Localized message, or the unchanged source.
 */
return static function ( string $message ): string {
    // Literal calls let the host's normal translation extractor collect every source string.
    switch ( $message ) {
        case 'Private mode must be boolean.':
            return __( 'Private mode must be boolean.', 'tuerchenzeit' );
        case 'Invalid authentication provider.':
            return __( 'Invalid authentication provider.', 'tuerchenzeit' );
        case 'The plugin must be installed in a stable slug directory.':
            return __( 'The plugin must be installed in a stable slug directory.', 'tuerchenzeit' );
        case 'Invalid GitHub repository URL.':
            return __( 'Invalid GitHub repository URL.', 'tuerchenzeit' );
        case 'The private update could not be authorized. Check the repository credentials and refresh updates.':
            return __( 'The private update could not be authorized. Check the repository credentials and refresh updates.', 'tuerchenzeit' );
        case 'Could not create the update download file.':
            return __( 'Could not create the update download file.', 'tuerchenzeit' );
        case 'The private update download failed. Check credentials and try again.':
            return __( 'The private update download failed. Check credentials and try again.', 'tuerchenzeit' );
        case 'Could not access the update filesystem.':
            return __( 'Could not access the update filesystem.', 'tuerchenzeit' );
        case 'GitHub release does not contain the plugin main file.':
            return __( 'GitHub release does not contain the plugin main file.', 'tuerchenzeit' );
        case 'Could not prepare the GitHub release package.':
            return __( 'Could not prepare the GitHub release package.', 'tuerchenzeit' );
        case 'See the release on GitHub.':
            return __( 'See the release on GitHub.', 'tuerchenzeit' );
        default:
            return $message;
    }
};
