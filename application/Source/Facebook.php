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
    Myjive\Core\Notification;

/**
 * Facebook source handler
 *
 * @category Myjive
 * @package SocialFeed
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class Facebook implements SourceInterface {

    use SourceTrait;

    /**
     * Facebook connection
     * 
     * @var \Facebook\Facebook
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
        $result = array('status' => false, 'errors' => array());

        try {
            $feed = $this->fetch();
            
            if (empty($feed)) {
                $result['errors'][] = __('No data to fetch', MYJIVE_KEY);
            } else {
                $result['status'] = true;
            }
            //check token expiration data and also update the notification
            $this->checkToken();
        } catch (\Exception $e) {
            // When Graph returns an error
            $result['errors'][] = __($e->getMessage(), MYJIVE_KEY);
        }

        return $result;
    }

    /**
     * Fetch Facebook feed
     * 
     * @return \Facebook\FacebookResponse
     * 
     * @throws \Exception
     * 
     * @access public
     */
    public function fetch() {
        //get Facebook connector
        $facebook = $this->getConnection();
        
        $fields = apply_filters(
                'myjive-fb-feed-fields', 'id,message,from,created_time'
        );
        $response = $facebook->get(
                '/' . $this->getOption('ID') . '/feed?fields=' . $fields
        );

        if ($response->isError()) {
            Throw new \Exception($response->getDecodedBody()['error']);
        } else { //iterate through each post and get attachments
            $feed = array();
            foreach ($response->getDecodedBody()['data'] as $status) {
                $attachment = $facebook->get("/{$status['id']}/attachments");
                $feed[] = (object) array(
                    'status' => $status,
                    'attachments' => $attachment->getDecodedBody()['data']
                );
            }
            //cache feed
            $this->cacheFeed($feed);
        }
        
        
        return $feed;
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
        foreach($feed as $status) {
            Cache::getInstance()->add(
                    'Facebook', $status->status['id'], $status
            );
        }
        //save cache
        Cache::getInstance()->save('Facebook');
    }
    
    /**
     * Check access token
     * 
     * If access token expires within next 96 hours, the notification will be
     * saved.
     * 
     * @return void
     * 
     * @access public
     */
    public function checkToken() {
        if ($this->getOption('accessToken')) {
            $facebook = $this->getConnection();
            $metadata = $facebook->getOAuth2Client()->debugToken(
                    $this->getOption('accessToken')
            );

            $expire = new \DateTime();
            $expire->setTimestamp(time() + 96 * 3600);
            
            if ($expire >= $metadata->getField('expires_at')) {
                Notification::getInstance()->add(
                        '500',
                        __('Facebook token expires in 96 hours', MYJIVE_KEY)
                );
            } else {
                Notification::getInstance()->remove('500');
            }
        }
    }
    
    /**
     * Check if Facebook status is expired
     * 
     * Simply sums the Facebook status created time and predefined cache
     * lifetime. The cache lifetime can be modified with a filter
     * "myjive-cache-lifetime". Default cache lifetime is 30 days.
     * 
     * @param \stdClass $status
     * @param timestap  $lifetime
     * 
     * @return boolean
     * 
     * @access public
     */
    public function isExpired($status, $lifetime) {
        $created = strtotime($status->status['created_time']);
        
        return ($created + $lifetime >= time());
    }

    /**
     * Get Facebook connection
     * 
     * Create new if not established yet
     * 
     * @return \Facebook\Facebook
     * 
     * @access public
     */
    public function getConnection() {
        if (is_null($this->connection)) {
            $this->connection = new \Facebook\Facebook([
                'app_id' => $this->getOption('appID'),
                'app_secret' => $this->getOption('appSecret'),
                'default_graph_version' => 'v2.4',
                'default_access_token' => $this->getOption('accessToken')
            ]);
        }

        return $this->connection;
    }

}