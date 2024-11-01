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
 * Facebook social feed
 *
 * @category Myjive
 * @package FeedPost
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class Facebook implements TypeInterface {
    
    use TypeTrait;

    /**
     * Feed type
     */
    const TYPE = 'Facebook';
    
    /**
     * Base URL to Facebook
     */
    const BASE_URL = 'https://facebook.com';
    
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
            'post_content' => $item->status['message']
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
            'post_date' => $item->status['created_time'],
            'post_content' => $item->status['message'],
            'post_title' => 'Facebook: ' . $id,
            'post_status' => apply_filters(
                    'myjive-facebook-post-status', 
                    $this->getOption('Setting.postStatus', 'publish'),
                    $item
            ),
            'post_type' => \Myjive\FeedPost\POST_TYPE,
            'post_mime_type' => 'feed/facebook',
            'tax_input' => array(\Myjive\FeedPost\TAXONOMY => $this->term)
        ));
        
        if (!is_a($feedId, '\WP_Error')) {
            //insert metadata - URL to the post
            add_post_meta($feedId, 'url', self::BASE_URL . '/' . $id);
            
            //add media element if present
            $this->insertMediaMeta($feedId, $item);
        }
        
        return $feedId;
    }
    
    /**
     * Insert Facebook attachment
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
        
        foreach ($item->attachments as $attachment) {
            if (!empty($attachment['media']['image'])) {
                $result = add_post_meta(
                        $feedId, 'media', $attachment['media']['image']['src']
                );
                break; //insert only the first element
            }
        }
        
        return $result;
    }

}