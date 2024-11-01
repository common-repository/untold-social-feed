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
 * Google source handler
 *
 * @category Myjive
 * @package SocialFeed
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class Youtube implements SourceInterface {

    use SourceTrait;

    /**
     * Google connection
     * 
     * @var \Google_Service_YouTube
     * 
     * @access protected 
     */
    protected $connection = null;

    /**
     * Validate Youtube connection
     * 
     * @return array
     * 
     * @access public
     */
    public function validate() {
        $result = array('status' => false, 'errors' => array());
        
        //execute the query
        try {
            $feed = $this->fetch();
            
            if (empty($feed)) {
                $result['errors'][] = __('No data to fetch', MYJIVE_KEY);
            } else {
                $result['status'] = true;
            }
        } catch (\Exception $e) {
            $result['errors'][] = __($e->getMessage(), MYJIVE_KEY);
        }

        return $result;
    }
    
    /**
     * Fetch Youtube feed
     * 
     * @return mized
     * 
     * @access public
     */
    public function fetch() {
        //prepare Youtube query
        $query = $this->getQuery();

        //execute the query
        $response = call_user_func_array($query->callback, array(
            'id,snippet', $query->params
        ));
        
        //cache feed
        $this->cacheFeed($response->getItems());
        
        return $response->getItems();
    }
    
    /**
     * Check if Youtube video is expired
     * 
     * Simply sums the Youtube video created time and predefined cache
     * lifetime. The cache lifetime can be modified with a filter
     * "myjive-cache-lifetime". Default cache lifetime is 30 days.
     * 
     * @param \stdClass $video
     * @param timestap  $lifetime
     * 
     * @return boolean
     * 
     * @access public
     */
    public function isExpired($video, $lifetime) {
        $created = strtotime($video->publishedAt);
        
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
        foreach($feed as $video) {
            if (is_a($video, '\Google_Service_YouTube_PlaylistItem')) {
                $id = $video->getId();
            } else {
                $id = $video->getId()->getVideoId();
            }
            Cache::getInstance()->add(
                    'Youtube', 
                    $id, 
                    $video->getSnippet()->toSimpleObject()
            );
        }
        //save cache
        Cache::getInstance()->save('Youtube');
    }

    /**
     * Prepare query
     * 
     * Prepare Youtube query based on "queryType". The default type is regular
     * search.
     * 
     * @return \stdClass
     * 
     * @access protected
     */
    protected function getQuery() {
        $query = (object) array('callback' => null, 'params' => array());
        $youtube = $this->getConnection();

        switch ($this->getOption('queryType')) {
            case 'playlist':
                $query->callback = array(
                    $youtube->playlistItems, 'listPlaylistItems'
                );
                $query->params['playlistId'] = $this->getOption('query');
                break;

            case 'channel':
                $query->callback = array($youtube->search, 'listSearch');
                $query->params['channelId'] = $this->getOption('query');
                break;

            default:
                $query->callback = array($youtube->search, 'listSearch');
                $query->params['q'] = $this->getOption('query');
                break;
        }

        return $query;
    }

    /**
     * Get youtube connection
     * 
     * Extablish new if not set yet
     * 
     * @return \Google_Service_YouTube
     * 
     * @access public
     */
    public function getConnection() {
        if (is_null($this->connection)) {
            $client = new \Google_Client();
            $client->setDeveloperKey($this->getOption('appKey'));
            // Define an object that will be used to make all API requests.
            $this->connection = new \Google_Service_YouTube($client);
        }

        return $this->connection;
    }

}