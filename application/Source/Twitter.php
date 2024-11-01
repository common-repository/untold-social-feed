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

use Myjive\Core\Cache;

/**
 * Twitter source handler
 *
 * @category Myjive
 * @package SocialFeed
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class Twitter implements SourceInterface {

    use SourceTrait;

    /**
     * Twitter base API URL
     */
    const TWITTER_API = 'https://api.twitter.com/1.1';

    /**
     * Twitter search URI
     */
    const SEARCH_URI = '/search/tweets.json';

    /**
     * Twitter user timeline URI
     */
    const TIMELINE_URI = '/statuses/user_timeline.json';

    /**
     *
     * @var type 
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
        
        if (!empty($response->errors)) {
            foreach($response->errors as $error) {
                $result['errors'][] = __($error->message, MYJIVE_KEY);
            }
        } elseif (empty($response)) {
            $result['errors'][] = __('No data to fetch', MYJIVE_KEY);
        } else {
            $result['status'] = true;
        }
        
        return $result;
    }
    
    /**
     * Fetch twitter data
     * 
     * Get data either from the Twitter or local cache.
     * 
     * @return \stClass
     * 
     * @access public
     */
    public function fetch() {
        //get connection to the Twitter
        $connection = $this->getConnection();
        //prepare query object
        $query = $this->getQuery();

        //execute the API call
        $response = json_decode(
                $connection->setGetfield($query->param)
                        ->buildOauth($query->URL, 'GET')
                        ->performRequest()
        );
        
        //cache the response
        if (is_a($response, '\stdClass') && isset($response->statuses)) {
            //if search type
            $this->cacheFeed($response->statuses);
        } elseif (is_array($response) || empty($response->errors)) {
            //if no error, save user's feed
            $this->cacheFeed($response);
        }
        
        return $response;
    }
    
    /**
     * Check if Twitter message is expired
     * 
     * Simply sums the Twitter message created time and predefined cache
     * lifetime. The cache lifetime can be modified with a filter
     * "myjive-cache-lifetime". Default cache lifetime is 30 days.
     * 
     * @param \stdClass $twitt
     * @param timestap  $lifetime
     * 
     * @return boolean
     * 
     * @access public
     */
    public function isExpired($twitt, $lifetime) {
        $created = strtotime($twitt->created_at);
        
        return ($created + $lifetime >= time());
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
        foreach($feed as $twitt) {
            Cache::getInstance()->add('Twitter', $twitt->id, $twitt);
        }
        //save cache
        Cache::getInstance()->save('Twitter');
    }

    /**
     * Prepare query object
     * 
     * Takes in consideration "queryType" (search or timeline) where search is
     * a default value; and "query" value which can be either search string or
     * user's screen name.
     * 
     * The return value is a object with "URL" and "param" properties.
     * 
     * @return \stdClass
     * 
     * @access protected
     */
    protected function getQuery() {
        $query = (object) array('URL' => self::TWITTER_API, 'param' => '?');

        switch ($this->getOption('queryType')) {
            case 'timeline':
                $query->URL .= self::TIMELINE_URI;
                $query->param .= 'screen_name=';
                break;

            default:
                $query->URL .= self::SEARCH_URI;
                $query->param .= 'q=';
                break;
        }
        $query->param .= urlencode($this->getOption('query'));

        return $query;
    }

    /**
     * Get Twitter connector
     * 
     * If connector is not defined, initialize it
     * 
     * @return \TwitterAPIExchange
     * 
     * @access public
     */
    public function getConnection() {
        if (is_null($this->connection)) {
            //initialize the connection to twitter API
            $this->connection = new \TwitterAPIExchange(array(
                'oauth_access_token' => $this->getOption('accessToken'),
                'oauth_access_token_secret' => $this->getOption(
                        'accessTokenSecret'
                ),
                'consumer_key' => $this->getOption('consumerKey'),
                'consumer_secret' => $this->getOption('consumerSecret')
            ));
        }

        return $this->connection;
    }

}