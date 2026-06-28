<?php
/**
 * Uninstall cleanup.
 *
 * @package AnnouncementBar
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'announcement-bar_options' );
