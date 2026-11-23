<?php
/**
 * Headless: this WordPress install has no public front end. Send any visitor
 * who reaches wp.m1o.at to the real site. wp-admin and the REST API are not
 * affected (they don't use this template).
 *
 * @package RaveenthiranHeadless
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

wp_redirect( 'https://raveenthiran.com', 302 );
exit;
