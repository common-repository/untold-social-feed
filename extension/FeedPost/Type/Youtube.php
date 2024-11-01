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
 * Youtube social feed
 *
 * @category Myjive
 * @package FeedPost
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class Youtube implements TypeInterface {
    
    use TypeTrait;
    
    /**
     * Feed type
     */
    const TYPE = 'Youtube';
    
    /**
     * Base URL to a video
     */
    const BASE_URL = 'https://www.youtube.com/watch?v=';

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
            'post_content' => $item->description,
            'post_excerpt' => $item->title
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
            'post_date' => $item->publishedAt,
            'post_content' => $item->description,
            'post_excerpt' => $item->title,
            'post_title' => 'Youtube: ' . $id,
            'post_status' => apply_filters(
                    'myjive-youtube-post-status', 
                    $this->getOption('Setting.postStatus', 'publish'),
                    $item
            ),
            'post_type' => \Myjive\FeedPost\POST_TYPE,
            'post_mime_type' => 'feed/youtube',
            'tax_input' => array(\Myjive\FeedPost\TAXONOMY => $this->term)
        ));
        
        if (!is_a($feedId, '\WP_Error')) {
            //insert metadata - URL to the post
            add_post_meta($feedId, 'url', self::BASE_URL . $id);
            
            //add media element
            $this->insertMediaMeta($feedId, $item);
        }
        
        return $feedId;
    }
    
    /**
     * Insert Youtube video thumbnail
     * 
     * @param int       $feedId
     * @param \stdClass $item
     * 
     * @return int|boolean
     * 
     * @access protected
     */
    protected function insertMediaMeta($feedId, $item) {
        if (isset($item->thumbnails['standard'])) {
            $media = $item->thumbnails['standard']['url'];
        } elseif (isset($item->thumbnails['high'])) {
            $media = $item->thumbnails['high']['url'];
        } elseif (isset($item->thumbnails['medium'])) {
            $media = $item->thumbnails['medium']['url'];
        } else {
            $media = $item->thumbnails['default']['url'];
        }
        
        return add_post_meta($feedId, 'media', $media);
    }

}