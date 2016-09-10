<?php
/*
* Plugin Name:       WP Anti-Detritus
* Description:       Ditch the crap in the HTML output and admin area of WordPress.
* Version:           1.0
* Author:            James M. Joyce, Flashpoint Computer Services, LLC
* Author URI:        http://ww.flashpointcs.net
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
*/

if(!defined('ABSPATH')) { exit; }

add_action('admin_bar_menu', function($wp_admin_bar) { $wp_admin_bar->remove_node( 'wp-logo' ); }, 999);
add_filter('admin_footer_text', function() { return ''; });
add_action('login_headerurl', function() { return home_url(); });
add_filter('get_image_tag_class', function($class, $id, $align, $size) { return 'align'.esc_attr($align); }, 10, 4);

add_filter('body_class', function($classes) {
   global $post;
   if(isset($post) && is_singular()) {
      $classes[] = $post->post_name;
   }
   return $classes;
}, 10, 1);

add_filter('wp_headers', function($headers) {
   unset($headers['X-Pingback']);
   return $headers;
}, 10, 1);

add_action('wp_loaded', function() {
   global $wp_widget_factory;
   if(has_filter('wp_head', 'wp_widget_recent_comments_style')) {
      remove_filter('wp_head', 'wp_widget_recent_comments_style');
   }
   if(isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
      remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
   }
});

add_action('init', function() {
   remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
   remove_action('wp_head', 'index_rel_link');
   remove_action('wp_head', 'parent_post_rel_link');
   remove_action('wp_head', 'print_emoji_detection_script', 7);
   remove_action('wp_print_styles', 'print_emoji_styles');
   remove_action('wp_head', 'rel_canonical');
   remove_action('wp_head', 'rest_output_link_wp_head');
   remove_action('wp_head', 'rsd_link');
   remove_action('wp_head', 'wlwmanifest_link' );
   remove_action('wp_head', 'wp_generator');
   remove_action('wp_head', 'wp_oembed_add_discovery_links');
   remove_action('wp_head', 'wp_shortlink_wp_head');
   add_filter('json_enabled', '__return_false');
   add_filter('json_jsonp_enabled', '__return_false');
   add_filter('rest_enabled', '__return_false');
   add_filter('rest_jsonp_enabled', '__return_false');
   if(function_exists('visual_composer')) {
      remove_action('wp_head', array(visual_composer(), 'addMetaData'));
   }
});
