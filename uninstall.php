<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

// cleanup data
delete_metadata( 'user', 0, 'meaty_avatar_tag', '', true );
delete_site_transient( 'baconmockup-tags' );
