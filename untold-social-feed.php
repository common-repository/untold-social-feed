<?php

/**
  Plugin Name: Untold Social Feed
  Description: Social feed
  Version: 0.3
  Author: Untold Digital. <untold@untold-digital.com>
  Author URI: http://www.untold-digital.com

  -------------
  Copyright (C) <2015>  Myjive Inc. <info@myjive.com>

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

 */

namespace Myjive;

/**
 * Main Plugin Class
 *
 * Define and initialize plugin as well bootstrap the the admin UI
 *
 * @category Myjive
 * @package SocialFeed
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @author Bilal Duckett <bilal.duckett@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class SocialFeed {
    
    /**
     * Plugin page key
     */
    const PLUGIN_ID = 'myjive-social-feed';
    
    /**
     * Social feed cron job
     */
    const CRON_KEY = 'myjive-social-feed-cron';

    /**
     * Single instance of itself
     * 
     * @var Myjive\SocialFeed
     * 
     * @access private
     */
    private static $_instance = null;
    
    /**
     * Collection of autoload classes
     * 
     * List of all classed listed in autoload-class.php file
     * 
     * @var array
     * 
     * @access private 
     */
    private $_autoloader = array();
    
    /**
     * Social feed options
     * 
     * All settings related to Myjive Social Feed are stored in 
     * myjive-social-feed option in database organized as following:
     * [group-name]
     *    [option 1] => [value 1]
     *               ...
     *    [option N] => [value N]
     * 
     * Where [group-name] is either twitter, facebook, instagram, youtube or
     * settings.  
     * 
     * @var arry
     * 
     * @access private 
     */
    private $_options = null;
    
    /**
     * Construct the Myjive\SocialFeed
     * 
     * @return void
     * 
     * @access protected
     */
    protected function __construct() {
        if (is_admin()) {
            //manager Admin Menu
            if (is_multisite() && is_network_admin()) {
                add_action('network_admin_menu', array($this, 'addMenu'), 10);
            } else {
                add_action('admin_menu', array($this, 'addMenu'), 10);
            }

            //print required JS & CSS
            add_action('admin_print_scripts', array($this, 'printJavascript'));
            add_action('admin_print_styles', array($this, 'printStylesheet'));
            
            //ajax hook
            add_action('wp_ajax_' . self::PLUGIN_ID, array($this, 'ajax'));
        }
    }
    
    /**
     * Register MyjiveSocialFeed menu
     * 
     * If there is any notification in the Notification center, also show the
     * "counter bubble" next to the menu title.
     * 
     * @return void
     * 
     * @access public
     */
    public function addMenu() {
        $notification = Core\Notification::getInstance();
        if ($notification->count()) {
            $counter = '<span class="update-plugins">'
                     . '<span class="plugin-count">' . $notification->count()
                     . '</span></span>';
        } else {
            $counter = '';
        }
        
        //register menu
        add_menu_page(
            __('Social Feed', MYJIVE_KEY), 
            sprintf('%s %s', __('Social Feed', MYJIVE_KEY), $counter),
           'administrator', 
            self::PLUGIN_ID,
            array($this, 'renderUI'),
            plugins_url('menu-icon.png', __FILE__)
        );
        
        //also register submenu Settings
        add_submenu_page(
            self::PLUGIN_ID, 
            __('Settings', MYJIVE_KEY), 
            __('Settings', MYJIVE_KEY), 
            'administrator', 
            self::PLUGIN_ID, 
            array($this, 'renderUI')
        );
    }
    
    /**
     * Process the ajax call
     * 
     * @return string
     * 
     * @access public
     */
    public function ajax() {
        check_ajax_referer(__FILE__, '_ajax_nonce');

        switch(filter_input(INPUT_POST, 'type')) {
            case 'save':
                $response = $this->saveOption();
                break;
            
            case 'validate':
                $response = $this->validateSource();
                break;
            
            case 'clear-cache':
                $cache = Core\Cache::getInstance();
                $response = $cache->clearNamespace(
                        filter_input(INPUT_POST, 'source')
                );
                break;
            
            default:
                $response = null;
                break;
        }
       

        echo json_encode($response);
        exit;
    }
    
    /**
     * Print javascript files
     * 
     * Insert all necessary javascript files to the page header
     * 
     * @access public
     * 
     * @return void
     * 
     * @access public
     */
    public function printJavascript() {
        if (filter_input(INPUT_GET, 'page') == self::PLUGIN_ID) {
            wp_enqueue_script(
                'myjive-bootstrap', 
                plugins_url('javascript/bootstrap.min.js', __FILE__)
            );
            wp_enqueue_script(
                self::PLUGIN_ID, plugins_url('javascript/index.js', __FILE__)
            );
            wp_localize_script(self::PLUGIN_ID, 'myjiveSocialFeed', array(
                'nonce' => wp_create_nonce(__FILE__),
                'ajaxKey' => self::PLUGIN_ID,
                'baseurl' => admin_url('admin-ajax.php')
            ));
        }
    }

    /**
     * Print stylesheet files
     * 
     * Insert all necessary stylesheet files to the page header
     * 
     * @access public
     * 
     * @return void
     * 
     * @access public
     */
    public function printStylesheet() {
        if (filter_input(INPUT_GET, 'page') == self::PLUGIN_ID) {
            wp_enqueue_style(
                'myjive-bootstrap', 
                plugins_url('stylesheet/bootstrap.min.css', __FILE__)
            );
            wp_enqueue_style(
                self::PLUGIN_ID, plugins_url('stylesheet/index.css', __FILE__)
            );
        }
    }
    
    /**
     * Render MyjiveSocialFeed page
     * 
     * @return void
     * 
     * @access public
     */
    public function renderUI() {
        require(__DIR__ . '/view/index.phtml');
    }
    
    /**
     * Prepare UI label or phrase
     * 
     * @param string $phrase
     * @param mixed  $...
     * 
     * @return string
     * 
     * @access protected
     */
    protected function preparePhrase($phrase) {
        //prepare search patterns
        $search = array_fill(0, (func_num_args() - 1) * 2, null);
        array_walk($search, function(&$value, $index) {
            $value = '/\\' . ($index % 2 ? ']' : '[') . '/';
        });

        $replace = array();
        foreach (array_slice(func_get_args(), 1) as $key) {
            array_push($replace, "<{$key}>", "</{$key}>");
        }

        return preg_replace($search, $replace, $phrase, 1);
    }

    /**
     * Get social source handler
     * 
     * @param string $name
     * 
     * @return Myjive\Source\SourceInterface
     * 
     * @access public
     */
    public function getSource($name) {
        $classname = __NAMESPACE__ . "\Source\\{$name}";
        
        return $classname::getInstance($this->getOption($name, array()));
    }
    
    /**
     * Get option's value
     * 
     * @param string $option
     * @param mixed  $default
     * 
     * @return mixed
     * 
     * @access public
     */
    public function getOption($option, $default = null) {
        $chunks = explode('.', $option);
        $value = $this->getOptions();
        foreach ($chunks as $chunk) {
            if (isset($value[$chunk])) {
                $value = $value[$chunk];
            } else {
                $value = $default;
                break;
            }
        }
        
        return $value;
    }
    
    /**
     * Get all plugin options
     * 
     * If it was not initialized yet, do it
     * 
     * @return array
     * 
     * @access protected
     */
    protected function getOptions() {
        //makes sure that we read options from database ONLY when it is needed
        if (is_null($this->_options)) {
            //read plugin's data
            $this->_options = Core\Helper::readOption(self::PLUGIN_ID, array());
        }
        
        return $this->_options;
    }
    
    /**
     * Save UI option
     * 
     * @return array
     * 
     * @access protected
     */
    protected function saveOption() {
        $options = $this->getOptions();
        
        $group = filter_input(INPUT_POST, 'group');
        $option = filter_input(INPUT_POST, 'option');
        $options[$group][$option] = filter_input(INPUT_POST, 'value');
        
        return array(
            'status' => Core\Helper::updateOption(self::PLUGIN_ID, $options)
       );
    }
    
    /**
     * Validate social source
     * 
     * @return array
     * 
     * @access protected
     */
    protected function validateSource() {
        $source = $this->getSource(filter_input(INPUT_POST, 'source'));
        
        return $source->validate();
    }
    
    /**
     * Bootstrap all extensions
     * 
     * Iterate though all folders inside the extension folder and try to
     * bootstrap extensions one-by-one
     * 
     * @return void
     * 
     * @access protected
     */
    protected function bootstrapExtensions() {
        foreach(new \DirectoryIterator(__DIR__ . '/extension') as $dir) {
            if ($dir->isDir() && !$dir->isDot()) {
                $bootstrap = $dir->getPathname() . '/bootstrap.php';
                if (file_exists($bootstrap)) {
                    $this->_autoloader = array_merge(
                            $this->_autoloader, require($bootstrap)
                    );
                } else {
                    Throw new \Exception(
                        sprintf(
                            __('Invalid Extension %s', MYJIVE_KEY), 
                            $dir->getBasename()
                        )
                    );
                }
            }
        }
    }

    /**
     * Get MySocialFeed instance
     * 
     * @return Myjive\SocialFeed
     * 
     * @access public
     */
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::bootstrap();
        }
        
        return self::$_instance;
    }
    
    /**
     * Get social feed
     * 
     * If $namespace is not initialized, return the entire cache grouped by
     * social source.
     * 
     * @param string $namespace
     * 
     * @return array
     * 
     * @access public
     */
    public static function getFeed($namespace = null) {
        return new Core\Feed($namespace);
    }

    /**
     * Bootstrap the SocialFeed plugin
     * 
     * Register language domain and create a single instance of SocialFeed
     * object
     * 
     * @return void
     * 
     * @access public
     */
    public static function bootstrap() {
        if (is_null(self::$_instance)) {
            //register plugin core autoloader
            spl_autoload_register(__CLASS__ . '::autoload');
            //register language domain
            load_plugin_textdomain(MYJIVE_KEY, false, __DIR__ . '/lang');
            //create an instance of itself
            self::$_instance = new self;
            //read autoloader collection
            self::$_instance->_autoloader = require(
                    __DIR__ . '/autoload-map.php'
            );
            //bootstrap all available extensions
            self::$_instance->bootstrapExtensions();
            //check if cache env is alright
            Core\Cache::checkEnv();
        }
    }
    
    /**
     * Class autoloader
     * 
     * @param string $classname
     * 
     * @return void
     */
    public static function autoload($classname) {
        if (array_key_exists($classname, self::$_instance->_autoloader)) {
            require_once(self::$_instance->_autoloader[$classname]);
        } elseif (strpos($classname, 'Myjive') === 0) {
            $fname  = __DIR__ . '/application/';
            $fname .= str_replace(
                        array('Myjive', '\\'), array('', '/'), $classname
            );

            //try to include it. Otherwise PHP will die
            require_once($fname . '.php');
        }
    }
    
    /**
     * Activation Hook. Check for system requirements.
     *
     * @return void
     *
     * @access public
     */
    public static function activate() {
        global $wp_filesystem;
        
        //check PHP Version
        if (version_compare(PHP_VERSION, '5.4') == -1) {
            exit(__('PHP 5.4 or higher is required.', MYJIVE_KEY));
        }
        
        //create an wp-content/aam folder if does not exist
        WP_Filesystem(); //initialize the WordPress filesystem

        $wp_content = $wp_filesystem->wp_content_dir();

        //make sure that we have always content dir
        if ($wp_filesystem->exists($wp_content . '/myjive-cache') === false) {
            $wp_filesystem->mkdir($wp_content . '/myjive-cache');
        }
    }
    
    /**
     * Clean up the data
     *
     * @return void
     *
     * @access public
     * @static
     */
    public static function uninstall() {
        wp_clear_scheduled_hook(self::CRON_KEY);
    }

}

if (defined('ABSPATH')) {
    //define myjive social feed language key
    define('MYJIVE_KEY', 'myjive-social-feed');
    
    //register init hook for plugin bootstrap
    add_action('init', 'Myjive\SocialFeed::bootstrap');

    //schedule cron
    if (!wp_next_scheduled(SocialFeed::CRON_KEY)) {
        wp_schedule_event(
            time(), 
            'hourly', 
            SocialFeed::CRON_KEY
        );
    }
    add_action(SocialFeed::CRON_KEY, __NAMESPACE__ . '\Core\Cron::run');

    //activate & uninstall hook
    register_activation_hook(__FILE__, '\Myjive\SocialFeed::activate');
    register_uninstall_hook(__FILE__, '\Myjive\SocialFeed::uninstall');
}