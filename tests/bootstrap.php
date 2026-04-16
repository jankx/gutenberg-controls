<?php
/**
 * Bootstrap file for PHPUnit tests
 *
 * @package Jankx\Gutenberg\Tests
 */

// Load composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load Brain Monkey
require_once __DIR__ . '/../vendor/brain/monkey/inc/api.php';

// Define WordPress constants for testing
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content/');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . 'plugins/');
}

// Mock WordPress functions that might be called during tests
if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('_e')) {
    function _e($text, $domain = 'default') {
        echo $text;
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('has_block')) {
    function has_block($block_name, $post = null) {
        return true;
    }
}

if (!function_exists('register_block_type')) {
    function register_block_type($block_type, $args = []) {
        return true;
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
        return true;
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all') {
        return true;
    }
}

if (!function_exists('wp_localize_script')) {
    function wp_localize_script($handle, $object_name, $l10n) {
        return true;
    }
}

if (!function_exists('plugins_url')) {
    function plugins_url($path = '', $plugin = '') {
        return 'http://example.com/wp-content/plugins/' . $path;
    }
}

if (!function_exists('add_action')) {
    function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('has_action')) {
    function has_action($tag, $function_to_check = false) {
        return true;
    }
}

if (!function_exists('do_action')) {
    function do_action($tag, ...$arg) {
        return true;
    }
}

// Set test configuration
if (!defined('JANKX_TEST_MODE')) {
    define('JANKX_TEST_MODE', true);
}

// Initialize Brain Monkey\setUp() will be called in individual test setUp() methods
echo "Test bootstrap loaded.\n";
