<?php

// If  not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove any stored magic tag
delete_option('localnumber_tag');