<?php
/*
Plugin Name: Pimp My Snippet
Plugin URI: http://pehbehbeh.de/projekte/wp/pimp-my-snippet/
Description: Simple and lightweight syntax highlighting for WordPress.
Author: pehbehbeh
Version: 1.0
Author URI: http://pehbehbeh.de/
*/

class PimpMySnippet
{
    protected $geshi;
    protected $geshi_css_loaded;
    
    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'init'));
        add_filter('the_content', array($this, 'filter'), 1);
    }
    
    public function init()
    {
        require dirname(__FILE__) . '/geshi/geshi.php';
        
        // init GeSHi
        $this->geshi = new GeSHi();
        $this->geshi->enable_classes();
        $this->geshi->enable_keyword_links(false);
        $this->geshi->set_header_type(GESHI_HEADER_PRE_TABLE);
    }
    
    public function filter($input)
    {
        $pattern = "/\s*<pre(?:lang=[\"']([\w-]+)[\"']|line=[\"'](\d*)[\"']|escaped=[\"'](true|false)?[\"']|\s)+>(.*)<\/pre>\s*/siU";
        $output = preg_replace_callback($pattern, array($this, 'highlight'), $input);
        return $output;
    }
    
    public function highlight($args)
    {
        // trim arguments
        array_walk($args, create_function('&$arg', '$arg = trim($arg);'));
    
        // validate arguments
        $lang = (!empty($args[1])) ? $args[1] : 'none';
        $line = (!empty($args[2])) ? intval($args[2]) : 0;
        $escaped = ($args[3] == 'true') ? true : false;
        $snippet = ($escaped) ? htmlspecialchars_decode($args[4]) : $args[4];
    
        // GeSHi settings
        $line_numbers = ($line > 0) ? GESHI_NORMAL_LINE_NUMBERS : GESHI_NO_LINE_NUMBERS;
        $this->geshi->enable_line_numbers($line_numbers);
        $this->geshi->start_line_numbers_at($line);
        $this->geshi->set_source($snippet);
        $this->geshi->set_language($lang);
    
        // output
        $output = '<div class="pms">';
        if (!$this->geshi_css_loaded[$lang]) {
            $output .= '<style type="text/css">' . $this->geshi->get_stylesheet() . '</style>';
            $this->geshi_css_loaded[$lang] = true;
        }
        $output .= $this->geshi->parse_code();
        $output .= '</div>';
        return $output;
    }
}

new PimpMySnippet();
