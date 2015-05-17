<?php

namespace starise\Wordpress\Widgets;

use starise\Wordpress\DeepLinks as DeepLinks;

add_action('widgets_init', [__NAMESPACE__ . '\\TocWidget', 'init']);

class TocWidget extends \WP_Widget {

	const LANG_DOMAIN = 'deeplinks';

	public static function init()
	{
		$class = __CLASS__;
		register_widget($class);
	}

	public function __construct()
	{
		$widgetOptions = [
			'classname'   => 'toc-widget',
			'description' => __('Display table of contents', self::LANG_DOMAIN)
		];
		parent::__construct('deep-links', 'DeepLinks', $widgetOptions);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget($args, $instance)
	{
		$DeepLinks = DeepLinks::get_instance();
		$tocList   = $DeepLinks->getTableOfContents();

		if (!empty($tocList)) {
			extract($args);
			$title = apply_filters('widget_title', $instance['title']);

			echo $before_widget;
			if (! empty($title)) {
				echo $before_title . $title . $after_title;
			}

			echo $tocList;
			echo $after_widget;
		}
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param  array $new_instance Values just sent to be saved.
	 * @param  array $old_instance Previously saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance) {
		$instance = [];
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form($instance) {
		if (isset($instance['title'])) {
			$title = $instance['title'];
		} else {
			$title = __('Table of Contents', self::LANG_DOMAIN);
		}
		?>
		<p>
			<label for="<?= $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?= $this->get_field_id('title'); ?>" name="<?= $this->get_field_name('title'); ?>" type="text" value="<?= esc_attr($title); ?>" />
		</p>
		<?php
	}
}
