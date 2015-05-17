<?php
/**
 * Plugin Name: Deep Links
 * Description: Utility to add anchor links and IDs to all headings in content.
 * Version:     1.0.0
 * Author:      starise
 * Author URI:  http://stari.se
 */

define('DEEP_LINKS_PATH', plugin_dir_path(__FILE__));

function wp_deep_links_autoload()
{
	include_once(DEEP_LINKS_PATH . 'DeepLinks.php');
	include_once(DEEP_LINKS_PATH . 'Widgets/TocWidget.php');
}

wp_deep_links_autoload();
