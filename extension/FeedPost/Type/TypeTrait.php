<?php

/**
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

namespace Myjive\FeedPost\Type;

use Myjive\SocialFeed;

/**
 * Social type trait
 *
 * @category Myjive
 * @package FeedPost
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
trait TypeTrait {

    /**
     * Single instance of itself
     * 
     * @var Myjive\FeedPost\Type\TypeInterface
     * 
     * @access private 
     */
    private static $_instance = null;
    
    /**
     * Feed term Id
     * 
     * Each feed relates to one of the types like Facebook, Twitter etc.
     *
     * @var int|null
     * 
     * @access protected 
     */
    protected $term = null;

    /**
     * Initialize the type object
     * 
     * Check if object's type exists and if not, create a new term and keep
     * the Id in $this->term property
     * 
     * @return void
     * 
     * @access protected
     */
    protected function __construct() {
        $term = get_term_by('name', self::TYPE, \Myjive\FeedPost\TAXONOMY);
        
        if (empty($term)) { //create new term
            $result = wp_insert_term(self::TYPE, \Myjive\FeedPost\TAXONOMY);
            if (!is_a($result, '\WP_Error')) {
                $this->term = $result['term_id'];
            }
        } else {
            $this->term = $term->term_id;
        }
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
     * Get single instance of itself
     * 
     * @return  Myjive\FeedPost\Type\TypeInterface
     * 
     * @access public
     */
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

}