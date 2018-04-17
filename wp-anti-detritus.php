<?php
/*
 * Plugin Name: WP Anti-Detritus
 * Plugin URI: https://github.com/FPCSJames/wp-anti-detritus
 * GitHub Plugin URI: https://github.com/FPCSJames/wp-anti-detritus
 * Description: Ditch the crap in the HTML output and admin area of WordPress.
 * Version: 1.0.1
 * Author: James M. Joyce, Flashpoint Computer Services, LLC
 * Author URI: https://ww.flashpointcs.net
 * License: MIT
 * License URI: https://fpcs.mit-license.org
 */

if(!defined('ABSPATH')) { exit; }

add_action('admin_bar_menu', function($wp_admin_bar) { $wp_admin_bar->remove_node( 'wp-logo' ); }, 999);
add_filter('admin_footer_text', '__return_null');
add_action('login_headerurl', function() { return home_url(); });
add_filter('get_image_tag_class', function($class, $id, $align, $size) { return 'align'.esc_attr($align); }, 10, 4);
add_filter('emoji_svg_url', '__return_false');

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
   wp_deregister_script('wp-embed');
   if(function_exists('visual_composer')) {
      remove_action('wp_head', array(visual_composer(), 'addMetaData'));
   }
   if(defined('W3TC') && W3TC) {
	   add_filter('w3tc_can_print_comment', '__return_false');
   }
});

add_action('wp_dashboard_setup', function() {
	remove_meta_box( 'woocommerce_dashboard_status', 'dashboard', 'normal');
});

if(class_exists('RevSliderFront')) {
   add_filter('revslider_meta_generator', '__return_null');
}
