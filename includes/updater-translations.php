<?php
/** Copy into the host adapter and replace the tuerchenzeit textdomain with its literal domain. */
defined( 'ABSPATH' ) || exit;
/**
 * Resolve an updater source string in the host textdomain.
 * @param string $message English updater message.
 * @return string Translated host message, or original source text.
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
        case 'Git Updater is active. If a deckerweb installation or update fails, temporarily deactivate Git Updater and retry. Reactivate it afterwards if other plugins or themes need it. Only leave it disabled when those extensions have another reliable update provider.':
            return __( 'Git Updater is active. If a deckerweb installation or update fails, temporarily deactivate Git Updater and retry. Reactivate it afterwards if other plugins or themes need it. Only leave it disabled when those extensions have another reliable update provider.', 'tuerchenzeit' );
        case 'Manage installed plugins':
            return __( 'Manage installed plugins', 'tuerchenzeit' );
        case 'Dismiss this notice':
            return __( 'Dismiss this notice', 'tuerchenzeit' );
        case 'Permission denied.':
            return __( 'Permission denied.', 'tuerchenzeit' );
        case 'Public catalog callbacks require both providers and cannot be used in private mode.':
            return __( 'Public catalog callbacks require both providers and cannot be used in private mode.', 'tuerchenzeit' );
        case 'The approved release could not be verified.':
            return __( 'The approved release could not be verified.', 'tuerchenzeit' );
        case 'The approved release changed. Refresh updates first.':
            return __( 'The approved release changed. Refresh updates first.', 'tuerchenzeit' );
        case 'Catalog diagnostics require a public catalog provider.':
            return __( 'Catalog diagnostics require a public catalog provider.', 'tuerchenzeit' );
        case 'Catalog approval':
            return __( 'Catalog approval', 'tuerchenzeit' );
        case 'Package download':
            return __( 'Package download', 'tuerchenzeit' );
        case 'Checksum verification':
            return __( 'Checksum verification', 'tuerchenzeit' );
        case 'Archive verification':
            return __( 'Archive verification', 'tuerchenzeit' );
        case 'The update server could not be reached. Try again later.':
            return __( 'The update server could not be reached. Try again later.', 'tuerchenzeit' );
        case 'The update request timed out. Try again later.':
            return __( 'The update request timed out. Try again later.', 'tuerchenzeit' );
        case 'The update server address could not be resolved.':
            return __( 'The update server address could not be resolved.', 'tuerchenzeit' );
        case 'The secure connection to the update server failed.':
            return __( 'The secure connection to the update server failed.', 'tuerchenzeit' );
        case 'The update server returned an unsuccessful HTTP response.':
            return __( 'The update server returned an unsuccessful HTTP response.', 'tuerchenzeit' );
        case 'The update provider did not return valid update data.':
            return __( 'The update provider did not return valid update data.', 'tuerchenzeit' );
        case 'The package does not match the approved checksum. Installation was stopped.':
            return __( 'The package does not match the approved checksum. Installation was stopped.', 'tuerchenzeit' );
        case 'The archive or plugin identity could not be verified. Installation was stopped.':
            return __( 'The archive or plugin identity could not be verified. Installation was stopped.', 'tuerchenzeit' );
        case 'Update stopped at %1$s: %2$s':
            /* translators: 1: update stage, 2: fixed explanation. */
            return __( 'Update stopped at %1$s: %2$s', 'tuerchenzeit' );
        case 'HTTP status: %d.':
            /* translators: %d: HTTP error status. */
            return __( 'HTTP status: %d.', 'tuerchenzeit' );
        case 'Invalid private download limits.':
            return __( 'Invalid private download limits.', 'tuerchenzeit' );
        case 'Invalid package cleanup provider.':
            return __( 'Invalid package cleanup provider.', 'tuerchenzeit' );
        case 'Safe package cleanup is unavailable for this update provider.':
            return __( 'Safe package cleanup is unavailable for this update provider.', 'tuerchenzeit' );
        case 'The update package identity, version or requirements could not be verified.':
            return __( 'The update package identity, version or requirements could not be verified.', 'tuerchenzeit' );
        default:
            return $message;
    }
};
