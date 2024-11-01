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

namespace Myjive\FeedPost\Type;

/**
 * Twitter social feed
 *
 * @category Myjive
 * @package FeedPost
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class Twitter implements TypeInterface {
    
    use TypeTrait;
    
    /**
     * Feed type
     */
    const TYPE = 'Twitter';
    
    /**
     * Base URL to Twitter
     */
    const BASE_URL = 'https://twitter.com';
    
    /**
     * Update the status
     * 
     * @param \WP_Post  $post
     * @param \stdClass $item
     * 
     * @retur int|\WP_Error
     * 
     * @access public
     */
    public function update(\WP_Post $post, \stdClass $item) {
        wp_update_post(array(
            'ID' => $post->ID,
            'post_content' => $item->text
        ));
    }

    /**
     * Insert the status
     * 
     * @param string    $id
     * @param \stdClass $item
     * 
     * @return int|\WP_Error
     * 
     * @access public
     */
    public function insert($id, \stdClass $item) {
        $feedId = wp_insert_post(array(
            'post_date' => date('Y-m-d H:i:s', strtotime($item->created_at)),
            'post_content' => $item->text,
            'post_title' => 'Twitter: ' . $id,
            'post_status' => apply_filters(
                    'myjive-twitter-post-status', 
                    $this->getOption('Setting.postStatus', 'publish'),
                    $item
            ),
            'post_type' => \Myjive\FeedPost\POST_TYPE,
            'post_mime_type' => 'feed/twitter',
            'tax_input' => array(\Myjive\FeedPost\TAXONOMY => $this->term)
        ));
        
        if (!is_a($feedId, '\WP_Error')) {
            //insert metadata - URL to the post
            add_post_meta(
                $feedId, 
                'url', 
                self::BASE_URL . "/{$item->user->screen_name}/statuses/{$id}"
            );
            
            //add media element if present
            $this->insertMediaMeta($feedId, $item);
            
            //add Twitter screen name
            add_post_meta($feedId, 'screen_name', $item->user->screen_name);
        }
        
        return $feedId;
    }
    
     /**
     * Insert Twitter attachment
     * 
     * Get only the first attachment and skip the rest
     * 
     * @param int       $feedId
     * @param \stdClass $item
     * 
     * @return int|boolean
     * 
     * @access protected
     */
    protected function insertMediaMeta($feedId, $item) {
        $result = false;
        
        if (isset($item->entities->media)) {
            //insert only the first element
            $result = add_post_meta(
                    $feedId, 'media', $item->entities->media[0]->media_url
            );
        }
        
        return $result;
    }
    
}