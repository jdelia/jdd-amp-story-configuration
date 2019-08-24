<?php
/**
 * Plugin Name: JDD AMP Story Configuration
 *
 * @package   JDD_AMP_Story_Configuration
 * @author    Jackie D'Elia <jackie@jackiedelia.com>
 * @copyright 2019 D'Elia Media LLC
 * @license   GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Description: Configure the AMP Story experience
 * Plugin URI:  https://jackiedelia.com
 * Version:     0.4.0
 * Author:      Jackie D'Elia
 * Author URI:  https://jackiedelia.com
 * License:     GNU General Public License v2 (or later)
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Bail out if the AMP plugin is not installed.
if ( ! function_exists( 'amp_init' ) ) {
	return;
}

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'jdd_amp_story_plugin_flush_rewrites' );
/**
 * Runs when plugin is activated.
 *
 * @return void
 */
function jdd_amp_story_plugin_flush_rewrites() {
	jdd_amp_story_modify();
	flush_rewrite_rules();
}

add_action( 'init', 'jdd_amp_story_modify', 15 );

/**
 * Updates custom post type configuration of AMP Stories to enable archive pages.
 *
 * @return void
 */
function jdd_amp_story_modify() {
	if ( post_type_exists( 'amp_story' ) ) {
		$amp_modify_stories              = get_post_type_object( 'amp_story' ); // get the post type to modify.
		$amp_modify_stories->has_archive = true; // adds support for archive.
		if ( $amp_modify_stories ) {
			register_post_type( 'amp_story', $amp_modify_stories );
		}
	}
}


/**
* Enable landscape (desktop) support for stories that have the 'landscape' tag.
* Author: Weston Ruter, Google
* Author URI: https://weston.ruter.net/
* https://gist.github.com/westonruter/2ea25735be279b88c6f0946629d0240c
*/
add_filter(
	'amp_story_supports_landscape',
	function ( $supports, $post ) {
		if ( has_tag( 'landscape', $post ) ) {
			$supports = true;
		}
		return $supports;
	},
	10,
	2
);

add_action( 'init', 'jdd_amp_story_archive_settings' );

/**
 * Add Genesis CPT Archive Settings for AMP Stories.
 *
 * @see https://www.billerickson.net/genesis-archive-settings-for-custom-post-types/
 */
function jdd_amp_story_archive_settings() {
	if ( 'Genesis' === wp_get_theme()->parent_theme ) {
		add_post_type_support( 'amp_story', 'genesis-cpt-archives-settings' );
	}
}

add_filter( 'body_class', 'jdd_amp_story_archive' );

/**
 * Adds classes to body tag for AMP Story archive page.
 *
 * @param array $classes Current classes.
 *
 * @return array $classes Updated class array.
 */
function jdd_amp_story_archive( $classes ) {
	if ( is_post_type_archive( 'amp_story' ) ) {
		$classes[] = 'amp-stories-archive';
	}
	return $classes;
}
/**
 * Adds AMP stories to blog, category and tag archive pages.
 *
 * @param array $query Current query.
 *
 * @return array $query Updated query.
 */
function jdd_add_custom_types_to_taxonomies( $query ) {

	if ( is_home() || ( is_category() || is_tag() && $query->is_archive() ) ) {
		if ( empty( $query->query_vars['suppress_filters'] ) ) {
			$query->set(
				'post_type',
				array(
					'post',
					'amp_story',
				)
			);
		}
	}
	return $query;
}
add_filter( 'pre_get_posts', 'jdd_add_custom_types_to_taxonomies' );
