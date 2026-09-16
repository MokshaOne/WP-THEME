<?php
/**
 * Runs when the plugin is deleted from the Plugins screen.
 * Removes stored settings; the manual asset is removed with the plugin folder.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'dm_settings' );
