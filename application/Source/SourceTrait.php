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

namespace Myjive\Source;

/**
 * Twitter source trait
 * 
 * Common set of options and methods, shared by all source handlers
 *
 * @category Myjive
 * @package SocialFeed
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
Trait SourceTrait {

    /**
     * Instance of itself
     * 
     * @var Myjive\Source\SourceInterface
     * 
     * @access private 
     */
    private static $_instance = null;
    
    /**
     * Source options
     * 
     * @var array
     * 
     * @access private 
     */
    private $_options = array();

    /**
     * Initialize the source handler
     * 
     * @param array $options
     * 
     * @return void
     * 
     * @access protected
     */
    protected function __construct($options) {
        $this->init($options);
    }

    /**
     * Set source handler options
     * 
     * @param array $options
     * 
     * @return void
     * 
     * @access protected
     */
    protected function init($options) {
        $this->_options = $options;
    }
    
     /**
     * Get option's value
     * 
     * @param string $name
     * 
     * @return mixed
     * 
     * @access public
     */
    public function getOption($name) {
        return (isset($this->_options[$name]) ? $this->_options[$name] : null);
    }
    
    /**
     * Check if social source is active
     * 
     * Be default social source is deactivated
     * 
     * @return int
     * 
     * @access public
     */
    public function isActive() {
        return $this->getOption('active', 0);
    }

    /**
     * Get single instance of itself
     * 
     * @param array $options
     * 
     * @return Myjive\Source\SourceInterface
     * 
     * @access public
     */
    public static function getInstance(array $options = array()) {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($options);
        } elseif (!empty($options)) {
            self::$_instance->init($options);
        }
        
        return self::$_instance;
    }

}