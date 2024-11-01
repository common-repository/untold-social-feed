<?php

/**
  Extension: Myjive Frontend Feed List
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

namespace Myjive\FeedList;

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
     * Single instance of itself
     * 
     * @var Myjive\FeedList\Extension
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
        if (!is_admin()) {
            add_shortcode('untold-feed-list', array($this, 'renderList'));
        }
    }
    
    /**
     * Print out the feed list
     * 
     * @return string
     * 
     * @access public
     */
    public function renderList() {
        $result = '<pre>';
        foreach(SocialFeed::getFeed() as $item) {
            $result .= print_r($item, true);
        }
        $result .= '</pre>';
        
        return $result;
    }
    
    /**
     * Bootstrap the Feed List extension
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