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

namespace Myjive\Core;

use Myjive\Core\Cache,
    Myjive\SocialFeed;

/**
 * Plugin cron job
 * 
 * @category Myjive
 * @package SocialFeed
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class Cron {
    
    /**
     * Run the cron job
     * 
     * @return void
     * 
     * @access public
     */
    public static function run() {
        //1. Validate all social tokens
        //validate only Facebook token. Twitter & Youtube tokens do not expire
        $facebook = SocialFeed::getInstance()->getSource('Facebook');
        $facebook->checkToken();
        
        //2. Fetch social feed from Twitter
        $twitter = SocialFeed::getInstance()->getSource('Twitter');
        if ($twitter->isActive()) {
            $twitter->fetch();
        }
        //3. Fetch social feed from Facebook
        if ($facebook->isActive()) {
            $facebook->fetch();
        }
        //4. Fetch social feed from Youtube
        $youtube = SocialFeed::getInstance()->getSource('Youtube');
        if ($youtube->isActive()) {
            $youtube->fetch();
        }
        //5. Normalize the cache - clean up old feeds
        Cache::getInstance()->normalize();
    }
    
}