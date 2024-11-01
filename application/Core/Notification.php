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

use Myjive\Core\Helper as Helper;

/**
 * Plugin notification center
 * 
 * The main responsibility is to keep list of plugin notifications like
 * Facebook token expiration or file system issues.
 *
 * @category Myjive
 * @package SocialFeed
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class Notification {

    /**
     * Instance of itself
     * 
     * @var \Myjive\Core\Notification
     * 
     * @access private 
     */
    private static $_instance = null;

    /**
     * Notification list
     * 
     * @var array
     * 
     * @access protected 
     */
    protected $list = array();

    /**
     * Initialize the notification center
     * 
     * @return void
     * 
     * @access public
     */
    protected function __construct() {
        //fetch the notification list from the database first
        $this->list = Helper::readOption('myjive-notifications', array());
    }
    
    /**
     * Add new notification
     * 
     * The third parameter $runtime is a boolean value that indicates if the
     * notification should be saved to a database. The default value is false,
     * which means it is not a runtime notification and that is why it should
     * be saved to the database.
     * 
     * @param string  $code
     * @param string  $message
     * @param boolean $runtime
     * 
     * @return void
     * 
     * @access public
     */
    public function add($code, $message, $runtime = false) {
        $this->list[$code] = (object) array(
            'code' => $code,
            'message' => $message
        );
        
        if ($runtime === false) {
            Helper::updateOption('myjive-notifications', $this->list);
        }
    }
    
    /**
     * Remove the notification
     * 
     * @param string $code
     */
    public function remove($code) {
        if (isset($this->list[$code])) {
            unset($this->list[$code]);
        }
        //remove option if empty
        if (!$this->count()) {
            Helper::deleteOption('myjive-notifications');
        }
    }
    
    /**
     * Get list of notifications
     * 
     * @return array
     * 
     * @access public
     */
    public function getList() {
        return $this->list;
    }

    /**
     * Get notification count
     * 
     * @return int
     * 
     * @access public
     */
    public function count() {
        return count($this->list);
    }
    
    /**
     * Get single instance of itself
     * 
     * @return \Myjive\Core\Notification
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
