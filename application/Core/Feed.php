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
 * Social feed decorator
 * 
 * @category Myjive
 * @package SocialFeed
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class Feed extends \RecursiveIteratorIterator {
    
    /**
     * Feed namespace
     * 
     * It can be Facebook, Twitter, Youtube etc.
     * 
     * @var string
     * 
     * @access protected 
     */
    protected $namespace = null;

    /**
     * Initialize the Feed
     * 
     * @param string|null $namespace
     * 
     * @return void
     * 
     * @access public
     */
    public function __construct($namespace = null) {
        parent::__construct(
                new \RecursiveArrayIterator(
                        Cache::getInstance()->get($namespace)
                )
        );
        //if namespace is defined, than Cache::get() will return the just an
        //array with the list of items, that is why we should set the namespace
        //manually
        $this->setNamespace($namespace);
    }
    
    /**
     * Check if social feed exists
     * 
     * The entire social feed is grouped by the namespace like Facebook, Twitter
     * etc., where grouped feed is expected to be an array or \stdClass objects.
     * Each object represents a single social feed.
     * 
     * @return boolean
     * 
     * @access public
     */
    public function callHasChildren() {
        return is_array($this->getInnerIterator()->current());
    }
    
    /**
     * Set current namespace
     * 
     * Technically just set current feed group like Facebook, Twitter, Youtube
     * etc. This is used to instantiate the right Entity in current method.
     * 
     * @return \RecursiveIterator
     * 
     * @access public
     */
    public function callGetChildren() {
        $this->setNamespace($this->key());
        
        return parent::callGetChildren();
    }
    
    /**
     * Get current feed item
     * 
     * Instantiate current feed entity and return it
     * 
     * @return \Myjive\Entity\EntityIterface
     * 
     * @access public
     */
    public function current() {
        $entity = 'Myjive\Entity\\' . $this->getNamespace();
        
        return new $entity(parent::current());
    }
    
    /**
     * Set namespace
     * 
     * @param string $namespace
     * 
     * @return void
     * 
     * @access protected
     */
    protected function setNamespace($namespace) {
        $this->namespace = $namespace;
    }
    
    /**
     * Get namespace
     * 
     * @return string
     * 
     * @access protected
     */
    protected function getNamespace() {
        return $this->namespace;
    }

}