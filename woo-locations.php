<?php
/**
 * Plugin Name: WooCommerce Locations Pack
 * Plugin URI: https://wordpress.org/plugins/woo-locations/
 * Description: Extends WooCommerce with additional locations, such as UK counties, France provinces, etc.
 * Text Domain: woo-locations
 * Version: 1.9.8
 * Author: dangoodman
 * Author URI: https://tablerateshipping.com
 * Requires PHP: 7.1
 * Requires at least: 4.6
 * Tested up to: 6.6
 * WC requires at least: 5.0
 * WC tested up to: 9.1
 */

use WcLocations\Loader;

require_once(__DIR__.'/src/Loader.php');
Loader::load(__FILE__);