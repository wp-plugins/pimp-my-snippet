<?php
/*
Plugin Name: Pimp my Snippet
Plugin URI: http://pehbehbeh.de/projekte/wp/pimp-my-snippet/
Description: Simple and lightweight syntax highlighting for WordPress.
Author: pehbehbeh
Version: 0.1
Author URI: http://pehbehbeh.de/
*/
require_once('geshi/geshi.php');

add_action('plugins_loaded', 'pms_init');
add_filter('the_content', 'pms_filter', 1);

/**
 * init
 */
function pms_init() {
    global $geshi;
    
    // init GeSHi
    $geshi = new GeSHi();
    $geshi->enable_classes();
    $geshi->enable_keyword_links(false);
    $geshi->set_header_type(GESHI_HEADER_PRE_TABLE);
}

/**
 * content filter
 */
function pms_filter($input) {
    $pattern = "/\s*<pre(?:lang=[\"']([\w-]+)[\"']|line=[\"'](\d*)[\"']|escaped=[\"'](true|false)?[\"']|\s)+>(.*)<\/pre>\s*/siU";
    $output = preg_replace_callback($pattern, "pms_highlight", $input);
    return $output;
}

/**
 * highlighting
 */
function pms_highlight($args) {
    global $geshi, $geshi_css_loaded;

    // trim arguments
    array_walk($args, create_function('&$arg', '$arg = trim($arg);'));

    // validate arguments
    $lang = (!empty($args[1])) ? $args[1] : 'none';
    $line = (!empty($args[2])) ? intval($args[2]) : 0;
    $escaped = ($args[3] == 'true') ? true : false;
    $snippet = ($escaped) ? htmlspecialchars_decode($args[4]) : $args[4];

    // GeSHi settings
    $line_numbers = ($line > 0) ? GESHI_NORMAL_LINE_NUMBERS : GESHI_NO_LINE_NUMBERS;
    $geshi->enable_line_numbers($line_numbers);
    $geshi->start_line_numbers_at($line);
    $geshi->set_source($snippet);
    $geshi->set_language($lang);

    // output
    $output = '<div class="pms">';
    if (!$geshi_css_loaded[$lang]) {
        $output .= '<style type="text/css">' . $geshi->get_stylesheet() . '</style>';
        $geshi_css_loaded[$lang] = true;
    }
    $output .= $geshi->parse_code();
    $output .= '</div>';
    return $output;
}