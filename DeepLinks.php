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

	public function deepLinksToContent( $content )
	{
		if ( ! is_single() || is_page() ) {
			return $content;
		}

		$pattern = '#(?P<full_tag><(?P<tag_name>h\d)(?P<tag_extra>[^>]*)>(?P<tag_contents>[^<]*)</h\d>)#i';
		if ( preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER ) ) {
			$find = [];
			$replace = [];
			foreach( $matches as $match ) {
				if ( strlen( $match['tag_extra'] ) && false !== stripos( $match['tag_extra'], 'id=' ) ) {
					continue;
				}
				$find[]     = $match['full_tag'];
				$id         = sanitize_title( $match['tag_contents'] );
				$idAttr     = sprintf( ' id="%s"', $id );
				$anchorLink = sprintf( '<a class="deep-link" href="#%s"></a>', $id );
				$anchorLink = apply_filters( 'header_deep_link', $anchorLink, $id );
				$replace[]  = sprintf( '%1$s<%2$s%3$s%4$s>%5$s</%2$s>', $anchorLink, $match['tag_name'], $match['tag_extra'], $idAttr, $match['tag_contents'] );
			}
			$content = str_replace( $find, $replace, $content );
		}

		return $content;
	}
}
