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

namespace Myjive\FeedPost;

$autoload = array();

if (defined('ABSPATH')) {
    require __DIR__ . '/Extension.php';
    
    //define custom post type slug
    define(
        __NAMESPACE__ . '\POST_TYPE', 
        apply_filters('myjive-feed-post-type', 'myjive-social-feed')
    );
    //define custom taxonomy slug
    define(
        __NAMESPACE__ . '\TAXONOMY', 
        apply_filters('myjive-feed-taxonomy', 'myjive-feed-type')
    );
    
    //boostrap the extension
    Extension::bootstrap();
    
    $autoload = array(
        __NAMESPACE__ . '\Extension' => __DIR__ . '/Extension.php',
        __NAMESPACE__ . '\Type\Facebook' => __DIR__ . '/Type/Facebook.php',
        __NAMESPACE__ . '\Type\Twitter' => __DIR__ . '/Type/Twitter.php',
        __NAMESPACE__ . '\Type\Youtube' => __DIR__ . '/Type/Youtube.php',
        __NAMESPACE__ . '\Type\Instagram' => __DIR__ . '/Type/Instagram.php',
        __NAMESPACE__ . '\Type\TypeTrait' => __DIR__ . '/Type/TypeTrait.php',
        __NAMESPACE__ . '\Type\TypeInterface' => __DIR__ . '/Type/TypeInterface.php'
    );
}

return $autoload;