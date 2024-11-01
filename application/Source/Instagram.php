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

use Myjive\Core\Cache,
    MetzWeb\Instagram\Instagram as Connector;

/**
 * Instagram source handler
 *
 * @category Myjive
 * @package SocialFeed
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class Instagram implements SourceInterface {

    use SourceTrait;
    
    /**
     * Response code OK
     */
    const RESPONSE_OK = 200;

    /**
     * Instagram connection
     * 
     * @var \MetzWeb\Instagram\Instagram
     * 
     * @access protected 
     */
    protected $connection = null;

    /**
     * Validate the connection
     * 
     * @return array
     * 
     * @access public
     */
    public function validate() {
        //fetch source data
        $response = $this->fetch();
        
        $result = array('status' => false, 'errors' => array());
        
        if ($response->meta->code != self::RESPONSE_OK) {
            $result['errors'][] = __($response->meta->error_message, MYJIVE_KEY);
        } elseif (empty($response->data)) {
            $result['errors'][] = __('No data to fetch', MYJIVE_KEY);
        } else {
            $result['status'] = true;
        }
        
        return $result;
    }
    
    /**
     * Fetch twitter data
     * 
     * Get data either from the Instagram or local cache.
     * 
     * @return \stClass
     * 
     * @access public
     */
    public function fetch() {
        //get connection to the Instagram
        $connection = $this->getConnection();
        //prepare query object
        $query = $this->getQuery();

        //execute the API call
        $response = $connection->{$query->callback}($query->param);
        
        //cache the response
        if (is_a($response, '\stdClass') && !empty($response->data)) {
            //if search type
            $this->cacheFeed($response->data);
        }
        
        return $response;
    }
    
    /**
     * Check if Instagram media is expired
     * 
     * Simply sums the Instagram media created time and predefined cache
     * lifetime. The cache lifetime can be modified with a filter
     * "myjive-cache-lifetime". Default cache lifetime is 30 days.
     * 
     * @param \stdClass $media
     * @param timestap  $lifetime
     * 
     * @return boolean
     * 
     * @access public
     */
    public function isExpired($media, $lifetime) {
        return ($media->created_time + $lifetime >= time());
    }
    
    /**
     * Cache the feed
     * 
     * @param array $feed
     * 
     * @return void
     * 
     * @access protected
     */
    protected function cacheFeed(&$feed) {
        foreach($feed as $media) {
            Cache::getInstance()->add('Instagram', $media->id, $media);
        }
        //save cache
        Cache::getInstance()->save('Instagram');
    }

    /**
     * Prepare query object
     * 
     * Takes in consideration "queryType" (search or timeline) where search is
     * a default value; and "query" value which can be either search string or
     * user's screen name.
     * 
     * The return value is a object with "callback" and "param" properties.
     * 
     * @return \stdClass
     * 
     * @access protected
     */
    protected function getQuery() {
        $query = (object) array();

        switch ($this->getOption('queryType')) {
            case 'timeline':
                $query->callback = 'getUserMedia';
                break;

            default:
                $query->callback = 'getTagMedia';
                break;
        }
        $query->param = $this->getOption('query');

        return $query;
    }

    /**
     * Get Instagram connector
     * 
     * If connector is not defined, initialize it
     * 
     * @return \MetzWeb\Instagram\Instagram
     * 
     * @access public
     */
    public function getConnection() {
        if (is_null($this->connection)) {
            //initialize the connection to Instagram API
            $this->connection = new Connector(array(
                'apiKey' => $this->getOption('appKey'),
                'apiSecret' => $this->getOption('apiSecret'),
                'apiCallback' => '' //no need to specify callback
            ));
        }

        return $this->connection;
    }

}