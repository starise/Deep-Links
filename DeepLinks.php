<?php
/**
 * Plugin Name: WP Deep Links
 * Description: Adds anchor link and ID to all headings in content.
 * Version: 1.0.0
 * Author: starise
 * Author URI: http://stari.se
 */

namespace starise\Wordpress;

add_action( 'plugins_loaded', [ __NAMESPACE__ . '\\DeepLinks', 'init' ] );

class DeepLinks
{
	public $anchors = [];

	public static function init()
	{
		$class = __CLASS__;
		new $class;
	}

	public function __construct()
	{
		// Priority 100: runs after all shortcodes
		add_filter( 'the_content', [ $this, 'deepLinksToContent' ], 100 );
	}

	/**
	 * Wordpress filter for 'the_content'. Works on all posts and pages.
	 * Adds an id property to all headings, and prepends an anchor link before it.
	 * Creates as object an array of headings formatted like:
	 *
	 * $anchors = [
	 *   'depth'   => int Element depth (1 for h1, 2 for h2, ...)
	 *   'id'      => string DOM id property of heading
	 *   'content' => string Content inside of heading
	 * ];
	 *
	 * @param  string $content Post/Page content from database
	 * @return string          Filtered content to display
	 */
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
				$tagContent = $match['tag_contents'];
				$anchorLink = sprintf( '<a class="deep-link" href="#%s">%s</a>', $id, $tagContent );
				/**
				 * Filter for anchor link formatting.
				 *
				 * @param string $anchorLink Anchor link to append before heading
				 * @param string $tagContent Content inside heading
				 * @param string $id         DOM id property of current heading
				 */
				$anchorLink = apply_filters( 'heading_anchor_link', $anchorLink, $tagContent, $id );
				$replace[]  = sprintf( '%1$s<%2$s%3$s%4$s>%5$s</%2$s>', $anchorLink, $match['tag_name'], $match['tag_extra'], $idAttr, $match['tag_contents'] );
				$this->anchors[] = [
					'depth'   => (int)substr($match['tag_name'], 1),
					'id'      => $id,
					'content' => $match['tag_contents']
				];
			}
			$content = str_replace( $find, $replace, $content );
		}

		return $content;
	}

	/**
	 * Build HTML hierarchically formatted list of anchor links.
	 * Must work with an array of anchors formatted like this:
	 *
	 * $anchors = [
	 *   'depth'   => int Element depth (1 for h1, 2 for h2, ...)
	 *   'id'      => string DOM id property of heading
	 *   'content' => string Content inside of heading
	 * ];
	 *
	 * @param  array  $anchors array of anchors formatted as above
	 * @return string HTML formatted list of anchors
	 */
	public function getTableOfContents()
	{
		$tocList = '';

		if ( !empty( $this->anchors ) ) {
			$tocTagOpen    = '<ul>';
			$tocTagClose   = '</ul>';
			$anchors       = $this->anchors;
			$tocList       = $tocTagOpen;
			$startingDepth = $anchors[0]['depth'];
			$currentDepth  = $startingDepth;

			foreach ( $anchors as $anchor ) {
				$anchorId      = $anchor['id'];
				$anchorContent = $anchor['content'];
				$tocElement    = sprintf( '<li><a href="#%s">%s</a></li>', $anchorId, $anchorContent );

				// Evaluate depth opening or closing tag as appropriate
				if ( $anchor['depth'] > $currentDepth ) {
					$tocElement = sprintf( '%s%s', $tocTagOpen, $tocElement );
					$currentDepth++;
				} elseif ( $anchor['depth'] < $currentDepth ) {
					while ( $anchor['depth'] < $currentDepth ) {
						$tocElement = sprintf( '%s%s', $tocTagClose, $tocElement );
						$currentDepth--;
					}
				}
				$tocList .= $tocElement;
			}

			// Close all tags still open
			while ( $currentDepth >= $startingDepth ) {
				$tocList .= $tocTagClose;
				$currentDepth--;
			}
		}

		return $tocList;
	}
}
