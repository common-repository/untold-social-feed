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

namespace Myjive\Entity;

/**
 * Social entity trait
 * 
 * @category Myjive
 * @package SocialFeed
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
trait EntityTrait {
    
    /**
     * Raw social feed data
     * 
     * The data that is returned from the external API call.
     * 
     * @var \stdClass
     * 
     * @access private 
     */
    private $_rawData = null;
    
    /**
     * Initialize the entity
     * 
     * @param \stdClass $rawData
     * 
     * @return void
     * 
     * @access public
     */
    public function __construct(\stdClass $rawData) {
        $this->setRawData($rawData);
    }
    
    /**
     * Set raw data
     * 
     * @param \stdClass $rawData
     * 
     * @return void
     * 
     * @access protected
     */
    protected function setRawData(\stdClass $rawData) {
        $this->_rawData = $rawData;
    }
    
    /**
     * Get raw data
     * 
     * @return \stdClass
     * 
     * @access public
     */
    public function getRawData() {
        return $this->_rawData;
    }
}