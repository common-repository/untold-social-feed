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
 * Twitter source interface
 * 
 * All social source handlers implements this interface
 *
 * @category Myjive
 * @package SocialFeed
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
interface SourceInterface {

    /**
     * Validate the source
     * 
     * Get all defined options for this source and try to fetch data. If
     * result is not empty and there is no API errors, then this function
     * return TRUE
     * 
     * @return boolean
     * 
     * @access public
     */
    public function validate();
    
    /**
     * Check if social source is activated
     * 
     * @return boolean
     * 
     * @access public
     */
    public function isActive();
    
    /**
     * Check if social feed is expired
     * 
     * Simply sums the feed created time and predefined cache
     * lifetime. The cache lifetime can be modified with a filter
     * "myjive-cache-lifetime". Default cache lifetime is 30 days.
     * 
     * @param \stdClass $feed
     * @param timestap  $lifetime
     * 
     * @return boolean
     * 
     * @access public
     */
    public function isExpired($feed, $lifetime);
    
    /**
     * Fetch data from the source
     * 
     * If $force parameter is set to true, then ignore the local cache and
     * execute the external fetch
     * 
     * @return mixed
     * 
     * @access public
     */
    public function fetch();
    
    /**
     * Get source API connection
     * 
     * Get either already established API connection or create new.
     * 
     * @return object
     * 
     * @access public
     */
    public function getConnection();
    
}