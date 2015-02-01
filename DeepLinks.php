<?php
/**
 * Plugin Name: WP Deep Links
 * Description: Adds anchor link and ID to all headers in content.
 * Version: 1.0.0
 * Author: starise
 * Author URI: http://stari.se
 */

namespace starise\Wordpress;

add_action( 'plugins_loaded', [ __NAMESPACE__ . '\\DeepLinks', 'init' ] );

class DeepLinks
{
	public static function init()
	{
		$class = __CLASS__;
		new $class;
	}

	public function __construct()
	{
		add_filter( 'the_content', [ $this, 'deepLinksToContent' ] );
	}

	public function deepLinksToContent( $content ) { }
}
