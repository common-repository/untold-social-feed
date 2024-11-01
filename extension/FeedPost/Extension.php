<?php

/**
  Extension: Myjive Feed Custom Post Type
  Description: Defines custom post type and UI for the social feed
  Version: 0.1
  Author: Myjive Inc. <info@myjive.com>
  Author URI: http://myjive.com

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

namespace Myjive\FeedPost;

use Myjive\SocialFeed;

/**
 * Main extension class
 *
 * Define and initialize this extension as well bootstrap the the admin UI
 *
 * @category Myjive
 * @package FeedPost
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class Extension {

    /**
     * Custom taxonomy slug
     */
    const TAXONOMY = 'myjive-feed-type';
    
    /**
     * Single instance of itself
     * 
     * @var Myjive\FeedPost\Extension
     * 
     * @access private
     */
    private static $_instance = null;

    /**
     * Initialize the extension
     * 
     * @return void
     * 
     * @access protected
     */
    protected function __construct() {
        if (is_admin()) {
            //manager Admin Menu
            if (is_multisite() && is_network_admin()) {
                add_action('network_admin_menu', array($this, 'addMenu'), 11);
            } else {
                add_action('admin_menu', array($this, 'addMenu'), 11);
            }
            //register permalink control over the feed edit page
            add_filter(
                'get_sample_permalink_html', 
                array($this, 'filterFeedLink'), 10, 2
            );
            //make sure that there is no "Get Shortlink" for feed
            add_filter('get_shortlink', array($this, 'filterShortlink'), 10, 2);
            //add additional filter to the feed list
            add_action(
                    'restrict_manage_posts', array($this, 'renderListFilter')
            );
            //add custom metabox to stamp edit page
            add_action('add_meta_boxes', array($this, 'addMetabox'));
            //extend plugin UI
            add_action('myjive-settings-ui', array($this, 'renderSettings'));
        }
        
        //register custom post type for a social feed
        $this->registerSocialFeed();
        
        //register add social feed hook
        add_action('myjive-add-cache', array($this, 'addFeed'), 10, 3);
    }
    
    /**
     * Register post type and taxonomy
     * 
     * @return void
     * 
     * @access protected
     */
    protected function registerSocialFeed() {
        //register custom post type
        $this->registerPostType();
        //register custom taxonomy
        $this->registerTaxonomy();
    }
    
    /**
     * Register custom post type
     * 
     * This function checks if post type is not registered yet and register it.
     * Keep in consideration that the custom post type id can be modified with
     * filter "myjive-feed-post-type".
     * 
     * @return void
     * 
     * @access protected
     */
    protected function registerPostType() {
        //manage feed capability
        $cap = apply_filters('myjive-feed-list-cap', 'edit_posts');
        
        //register custom post type only if does not registered yet
        if (!in_array(POST_TYPE, get_post_types())) {
            register_post_type(POST_TYPE, array(
                'label' => __('Social Feed', MYJIVE_KEY),
                'public' => true,
                'exclude_from_search' => true,
                'show_ui' => true,
                'show_in_menu' => false,
                'show_in_nav_menus' => false,
                'show_in_admin_bar' => true,
                'capabilities' => array(
                    'create_posts' => uniqid(), //make sure that Add New is hidden
                    'edit_post' => $cap,
                    'read_post' => $cap,
                    'delete_post' => $cap,
                    'delete_posts' => $cap
                ),
                'taxonomies' => array('myjive-feed-type')
            ));
        }
    }
    
    /**
     * Register custom taxonomy
     * 
     * This function checks if taxonomy is not registered yet and register it.
     * Keep in consideration that the custom taxonomy id can be modified with
     * filter "myjive-feed-taxonomy".
     * 
     * @return void
     * 
     * @access protected
     */
    protected function registerTaxonomy() {
        //register custom taxonomy only if not registered
        if (!in_array(TAXONOMY, get_taxonomies())) {
            register_taxonomy(TAXONOMY, POST_TYPE, array(
                'label' => __('Social Feed Type', MYJIVE_KEY),
                'show_ui' => true,
                'show_in_menu' => false,
                'show_in_nav_menus' => false,
                'hierarchical' => true,
                'show_in_quick_edit' => true,
                'capabilities' => array(
                    'edit_terms' => uniqid() //no need to add new; cronjob does it
                )
            ));
        }
    }
    
    /**
     * Register All Feed menu
     * 
     * @return void
     * 
     * @access public
     */
    public function addMenu() {
        global $submenu;
        
        //also register submenu Settings
        add_submenu_page(
            SocialFeed::PLUGIN_ID, 
            __('All Feed', MYJIVE_KEY), 
            __('All Feed', MYJIVE_KEY), 
            apply_filters('myjive-feed-list-cap', 'edit_posts'), 
            'edit.php?post_type=' . POST_TYPE
        );
        
        //reverse the submenu so Settings go to the bottom
        //if more than one extension will be developed in future, replace this
        //functionality to reflect multiple submenus registration
        $submenu[SocialFeed::PLUGIN_ID] = array_reverse(
                $submenu[SocialFeed::PLUGIN_ID]
        );
    }
    
    /**
     * Render extra settings
     * 
     * @return void
     * 
     * @access public
     */
    public function renderSettings() {
        require(__DIR__ . '/view/settings.phtml');
    }
    
    /**
     * Get plugin option
     * 
     * @param string $option
     * @param mixed  $default
     * 
     * @return mixed
     * 
     * @access protected
     */
    protected function getOption($option, $default = null) {
        return SocialFeed::getInstance()->getOption($option, $default);
    }
    
    /**
     * Filter permalink HTML
     * 
     * If post type is a feed, overwrite the default Permalink HTML with custom
     * HTML that only allows to go to the feed original source.
     * 
     * @param string $html
     * @param int    $id
     * 
     * @return string
     * 
     * @access public
     */
    public function filterFeedLink($html, $id) {
        $post = get_post($id);
        
        if ($this->isFeed($post)) {
            //get direct URL
            $url = get_post_meta($post->ID, 'url', true);
            
            $html  = '<strong>' . __('Direct Link', MYJIVE_KEY) . ': </strong>';
            $html .= $url . '&nbsp;&nbsp;<a href="' . $url . '" ';
            $html .= 'target="_blank" class="button button-small">';
            $html .= __('View Feed', MYJIVE_KEY) . '</a>';
        }
        
        return $html;
    }
    
    /**
     * Filter post shortlink
     * 
     * If post type is a feed, then simply empty the shortlink to remove the
     * "Get Shortlink" button next to the permalink.
     * 
     * @param string $shortlink
     * @param int    $id
     * 
     * @return string|null
     * 
     * @access public
     */
    public function filterShortlink($shortlink, $id) {
        $post = get_post($id);
        
        if ($this->isFeed($post)) {
            $shortlink = null;
        }
        
        return $shortlink;
    }
    
    /**
     * Check if post is actually a feed
     * 
     * @param \WP_Post $post
     * 
     * @return boolean
     * 
     * @access protected
     */
    protected function isFeed($post) {
        $feed = false;
        if (is_a($post, '\WP_Post') && $post->post_type == POST_TYPE) {
            if (strpos($post->post_mime_type, 'feed/') === 0) {
                $feed = true;
            }
        }
        
        return $feed;
    }
    
    /**
     * Render additional list filter
     * 
     * If taxonomy name is "category", than do not register additional filter.
     * This covers the scenario when the feed post type is "post".
     * 
     * @return void
     * 
     * @access public
     */
    public function renderListFilter() {
        if (TAXONOMY != 'category') {
            $options = array(
                'show_option_all' => __('All Feed', MYJIVE_KEY),
                'hide_empty' => 0,
                'hierarchical' => 1,
                'show_count' => 0,
                'orderby' => 'name',
                'name' => 'term',
                'value_field' => 'term_id',
                'selected' => filter_input(INPUT_GET, 'term'),
                'taxonomy' => TAXONOMY
            );

            echo '<label class="screen-reader-text" for="term">';
            echo __('Filter By Feed Type', MYJIVE_KEY) . '</label>';

            wp_dropdown_categories($options);

            echo '<input type="hidden" name="taxonomy" ';
            echo 'value="' . TAXONOMY. '" />';
        }
    }
    
    /**
     * Register media metabox
     * 
     * @return void
     * 
     * @access public
     */
    public function addMetabox() {
        global $post;
        
        if ($this->isFeed($post)) {
            $media = get_post_meta($post->ID, 'media', true);

            if ($media) { //no need to show any addional metabox if no media
                add_meta_box(
                    'media-previe', __('Feed\'s Media', MYJIVE_KEY), 
                    array($this, 'renderMediaMetabox'), 
                    POST_TYPE,
                    'side',
                    'default',
                    array('media' => $media)
                );
            }
        }
    }
    
    /**
     * Render the media metabox content
     * 
     * @param \WP_Post $post
     * @param array    $metadata
     * 
     * @return void
     * 
     * @access public
     */
    public function renderMediaMetabox($post, $metadata) {
        echo '<img src="' . $metadata['args']['media'] . '" ';
        echo 'style="max-width: 100%" />';
    }
    
    /**
     * Add social feed
     * 
     * Add new feed if does not exist
     * 
     * @param string    $namespace Social source like Facebook, Twitter etc
     * @param string    $id        Social item id
     * @param \stdClass $item      Social item all data
     * 
     * @return void
     * 
     * @access public
     */
    public function addFeed($namespace, $id, $item) {
        $classname = __NAMESPACE__ . '\Type\\' . $namespace;
        $post = get_page_by_title($namespace . ': ' . $id, OBJECT, POST_TYPE);
        
        if ($post) {
            $classname::getInstance()->update($post, $item);
        } else {
            $classname::getInstance()->insert($id, $item);
        }
    }

    /**
     * Bootstrap the Feed Post extension
     * 
     * @return void
     * 
     * @access public
     */
    public static function bootstrap() {
        if (is_null(self::$_instance)) {
            //create an instance of itself
            self::$_instance = new self;
        }
    }

}