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

namespace Myjive\Core;

/**
 * WordPress core helper
 *
 * @category Myjive
 * @package SocialFeed
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class Helper {

    /**
     * Read option
     * 
     * Read option from the WordPress core database
     * 
     * @param string $name
     * @param mixed  $default
     * 
     * @return mixed
     * 
     * @access public
     */
    public static function readOption($name, $default = null) {
        if (is_multisite()) {
            $option = get_blog_option(get_current_blog_id(), $name, $default);
        } else {
            $option = get_option($name, $default);
        }

        return $option;
    }

    /**
     * Add option
     * 
     * Add option to the WordPress core database
     * 
     * @param string $name
     * @param mixed  $value
     * 
     * @return boolean
     * 
     * @access public
     */
    public static function addOption($name, $value) {
        if (is_multisite()) {
            $result = add_blog_option(get_current_blog_id(), $name, $value);
        } else {
            $result = add_option($name, $value);
        }

        return $result;
    }

    /**
     * Update option
     * 
     * Update option in the WordPress core database. Keep in mind that WordPress
     * will return false if option is tried to be updated with the same value.
     * 
     * @param string $name
     * @param mixed  $value
     * 
     * @return boolean
     * 
     * @access public
     */
    public static function updateOption($name, $value) {
        if (is_multisite()) {
            $result = update_blog_option(get_current_blog_id(), $name, $value);
        } else {
            $result = update_option($name, $value);
        }

        return $result;
    }
    
    /**
     * Delete option
     * 
     * Delete option in the WordPress core database.
     * 
     * @param string $name
     * 
     * @return boolean
     * 
     * @access public
     */
    public static function deleteOption($name) {
        if (is_multisite()) {
            $result = delete_blog_option(get_current_blog_id(), $name);
        } else {
            $result = delete_option($name);
        }

        return $result;
    }

}