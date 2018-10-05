<?php
/*
* Plugin Name: WP Anti-Detritus
* Plugin URI: https://github.com/FPCSJames/wp-anti-detritus
* GitHub Plugin URI: https://github.com/FPCSJames/wp-anti-detritus
* Description: Ditch the crap in the HTML output and admin area of WordPress.
* Version: 1.1.1
* Author: James M. Joyce, Flashpoint Computer Services, LLC
* Author URI: https://www.flashpointcs.net
* License: MIT
* License URI: https://fpcs.mit-license.org
*/

if(!defined('ABSPATH')) { exit; }

final class WP_Anti_Detritus {

   public function __construct() {
      add_action('admin_bar_menu', [$this, 'remove_admin_bar_items'], 999);
      add_action('wp_dashboard_setup', [$this, 'clean_wp_admin']);
      add_action('wp_loaded', [$this, 'clean_wp_head']);
      add_filter('body_class', [$this, 'add_slug_to_body_class']);
      add_filter('contextual_help', [$this, 'remove_contextual_help'], 999, 3);
      add_action('widgets_init', [$this, 'remove_default_widgets']);
      add_filter('wp_headers', [$this, 'remove_pingback_header']);

      add_action('login_headerurl', function() { return home_url(); });
      add_filter('admin_footer_text', '__return_null');
      add_filter('emoji_svg_url', '__return_false');
      add_filter('enable_post_by_email_configuration', '__return_false', 999);
      add_filter('feed_links_show_comments_feed', '__return_false');
      add_filter('get_image_tag_class', function($c, $i, $align, $s) { return 'align'.esc_attr($align); }, 10, 4);
      add_filter('jpeg_quality', function($v) { return 95; });
      add_filter('the_generator', '__return_empty_string');
      remove_action('welcome_panel', 'wp_welcome_panel');

      if(class_exists('RevSliderFront')) {
         add_filter('revslider_meta_generator', '__return_null');
      }
      if(defined('WPAD_DISABLE_REST') && WPAD_DISABLE_REST) {
         add_filter('rest_authentication_errors', [$this, 'restrict_rest_api_to_authenticated_users']);
      }
   }

   public function add_slug_to_body_class($classes) {
      global $post;
      if(isset($post) && is_singular()) {
         $classes[] = $post->post_name;
      }
      return $classes;
   }

   public function clean_wp_admin() {
      remove_meta_box('dashboard_primary', 'dashboard', 'side');
      remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
      remove_meta_box('dashboard_secondary', 'dashboard', 'normal');
      remove_meta_box('dashboard_quick_press', 'dashboard', 'side'); // Quick Draft
      remove_meta_box('wpseo-dashboard-overview', 'dashboard', 'normal'); // Yoast
      remove_submenu_page('themes.php', 'custom-header'); // Appearance > Header
      remove_submenu_page('themes.php', 'custom-background'); // Appearance > Background
      if(defined('ELEMENTOR_VERSION')) {
         remove_meta_box('e-dashboard-overview', 'dashboard', 'normal');
      }
      if(class_exists('WooCommerce')) {
         remove_meta_box('woocommerce_dashboard_status', 'dashboard', 'normal'); // WooCommerce
      }
      if(function_exists('tribe_get_events')) {
         remove_meta_box('tribe_dashboard_widget', 'dashboard', 'side'); // The Events Calendar
      }
      if(class_exists('OCEANWP_Theme_Class')) {
         remove_meta_box('owp_dashboard_news', 'dashboard', 'normal'); // OceanWP News
      }
   }

   public function clean_wp_head() {
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

      // Plugin-specific cleanup
      if(function_exists('visual_composer')) { // WPBakery Page Builder: remove generator tag
         remove_action('wp_head', [visual_composer(), 'addMetaData']);
      }
      if(defined('W3TC') && W3TC) { // W3 Total Cache: remove comment in footer
         add_filter('w3tc_can_print_comment', '__return_false');
      }
      if(class_exists('WPSEO_Frontend') && method_exists('WPSEO_Frontend', 'debug_mark')) { // Yoast SEO: remove comments in head
         remove_action('wpseo_head', [WPSEO_Frontend::get_instance(), 'debug_mark'], 2);
      }
      if(class_exists('WooCommerce')) { // WooCommerce: remove generator tag
         remove_action('wp_head', 'woo_version');
      }

      // Remove Recent Comments markup
      global $wp_widget_factory;
      if(has_filter('wp_head', 'wp_widget_recent_comments_style')) {
         remove_filter('wp_head', 'wp_widget_recent_comments_style');
      }
      if(isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
         remove_action('wp_head', [$wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style']);
      }

      wp_deregister_script('wp-embed');
   }

   public function remove_admin_bar_items($wp_admin_bar) {
      $wp_admin_bar->remove_node('wp-logo');
      $wp_admin_bar->remove_node('new-content');
   }

   public function remove_contextual_help($old_help, $screen_id, $screen) {
      $screen->remove_help_tabs();
      return $old_help;
   }

   public function remove_default_widgets() {
      unregister_widget('WP_Widget_Pages');
      unregister_widget('WP_Widget_Meta');
      unregister_widget('WP_Widget_Tag_Cloud');
      unregister_widget('WP_Widget_RSS');
      unregister_widget('WP_Widget_Calendar');
   }

   public function remove_pingback_header($headers) {
      unset($headers['X-Pingback']);
      return $headers;
   }

   public function restrict_rest_api_to_authenticated_users($result) {
      if(!empty($result)) {
         return $result;
      }
      if(!is_user_logged_in()) {
         return new WP_Error('rest_not_logged_in', 'API access is restricted to logged-in users.', ['status' => 401]);
      }
      return $result;
   }
}

new WP_Anti_Detritus();
