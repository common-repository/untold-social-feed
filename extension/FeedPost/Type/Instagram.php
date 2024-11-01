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
 * Instagram social feed
 *
 * @category Myjive
 * @package FeedPost
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class Instagram implements TypeInterface {
    
    use TypeTrait;
    
    /**
     * Feed type
     */
    const TYPE = 'Instagram';
    
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
            'post_content' => $item->caption->text
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
            'post_date' => date('Y-m-d H:i:s', $item->created_time),
            'post_content' => $item->caption->text,
            'post_title' => 'Instagram: ' . $id,
            'post_status' => apply_filters(
                    'myjive-instagram-post-status', 
                    $this->getOption('Setting.postStatus', 'publish'),
                    $item
            ),
            'post_type' => \Myjive\FeedPost\POST_TYPE,
            'post_mime_type' => 'feed/instagram',
            'tax_input' => array(\Myjive\FeedPost\TAXONOMY => $this->term)
        ));
        
        if (!is_a($feedId, '\WP_Error')) {
            //insert metadata - URL to the post
            add_post_meta($feedId, 'url', $item->link);
            
            //add media element if present
            $this->insertMediaMeta($feedId, $item);
            
            //add Instagram username
            add_post_meta($feedId, 'screen_name', $item->user->username);
        }
        
        return $feedId;
    }
    
     /**
     * Insert Instagram attachment
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
        if (!empty($item->images->standard_resolution)) {
            $media = $item->images->standard_resolution->url;
        } elseif (!empty($item->images->thumbnail)) {
            $media = $item->images->thumbnail->url;
        } else {
            $media = $item->images->low_resolution->url;
        }
       
        return add_post_meta($feedId, 'media', $media);
    }
    
}