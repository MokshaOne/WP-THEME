<?php
// #49 — minimal bootstrap: load only the pure, WP-free helpers (inc/lib.php).
if ( ! defined( 'ABSPATH' ) ) define( 'ABSPATH', __DIR__ . '/' ); // satisfy the guard
require dirname( __DIR__, 2 ) . '/inc/lib.php';
