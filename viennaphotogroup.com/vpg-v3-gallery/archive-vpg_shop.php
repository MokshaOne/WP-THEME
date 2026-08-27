<?php
/** VPG v2 · belt-and-suspenders redirect to the unified map */
if ( ! defined( 'ABSPATH' ) ) exit;
$type = ( get_post_type() === 'vpg_studio' ) ? 'studio' : 'shop';
wp_safe_redirect( add_query_arg( 'type', $type, get_post_type_archive_link( 'vpg_location' ) ), 301 );
exit;
