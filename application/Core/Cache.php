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

/**
 * Plugin cache center
 * 
 * @category Myjive
 * @package SocialFeed
 * @author Myjive Inc. <info@myjive.com>
 * @author Vasyl Martyniuk <vasyl.martyniuk@myjive.com>
 * @copyright Copyright C 2015 Myjive Inc.
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class Cache {

    /**
     * Instance of itself
     * 
     * @var Myjive\Core\Cache
     * 
     * @access private 
     */
    private static $_instance = null;
    
    /**
     * Base directory to cache
     * 
     * @var string
     * 
     * @access protected 
     */
    protected $basedir = \WP_CONTENT_DIR;
    
    /**
     * Entire cache
     * 
     * Cache is grouped by the source
     * 
     * @var array
     * 
     * @access protected 
     */
    protected $cache = null;
    
    /**
     * Initialize the core cache
     * 
     * @return void
     * 
     * @access protected
     */
    protected function __construct() {
        $this->basedir = \WP_CONTENT_DIR . '/myjive-cache';
        
        //initialize the cache
        $this->readCache();
    }
    
    /**
     * Get cache
     * 
     * If namespace is not defined, return the entire cache grouped by source
     * 
     * @param string $namespace
     * 
     * @return array
     * 
     * @access public
     */
    public function get($namespace = null) {
        if (is_null($namespace)) {
            $response = $this->cache;
        } elseif(isset($this->cache[$namespace])) {
            $response = $this->cache[$namespace];
        } else {
            $response = array();
        }
        
        return $response;
    }
    
    /**
     * Add feed to the cache
     * 
     * @param string $namespace
     * @param string $id
     * @param mixed  $item
     * 
     * @return void
     * 
     * @access public
     */
    public function add($namespace, $id, $item) {
        if (!isset($this->cache[$namespace])) {
            $this->cache[$namespace] = array();
        }
        
        //add the feed
        $this->cache[$namespace][$id] = $item;
        
        //allow to hook to the add cache action
        do_action('myjive-add-cache', $namespace, $id, $item);
    }
    
    /**
     * Clear cache by namespace
     * 
     * @param string $namespace
     * 
     * @return boolean
     * 
     * @access public
     */
    public function clearNamespace($namespace) {
        $filename = $this->basedir . '/' . $namespace . '.feed';
        
        if (file_exists($filename)) {
            $response = unlink($filename);
        } else {
            $response = false;
        }
        
        return $response;
    }
    
    /**
     * Normalize the cache
     * 
     * Remove all expired messages in the social feed
     * 
     * @return void
     * 
     * @access public
     */
    public function normalize() {
        foreach($this->cache as $namespace => $feed) {
            $source = \Myjive\SocialFeed::getInstance()->getSource($namespace);
            $lifetime = apply_filters(
                    'myjive-cache-lifetime', 2592000, $namespace
            );
            //iterate though each element of the feed and remove expired
            foreach($feed as $id => $element) {
                if ($source->isExpired($element, $lifetime)) {
                    unset($feed[$id]);
                }
            }
        }
        
        $this->save();
    }
    
    /**
     * Read cache
     * 
     * If $namespace is not defined, read the entire cache directory
     * 
     * @param string|null $namespace
     * 
     * @return void
     * 
     * @access protected
     */
    protected function readCache($namespace = null) {
        //if $namespace is null, read entire cache directory
        if (is_null($namespace)) {
            foreach (new \FilesystemIterator($this->basedir) as $file) {
                if (!in_array($file->getFilename(), array('.', '..'))) {
                    $this->readCacheFile($file->getPathname());
                }
            }
        } else {
            $this->readCacheFile($this->basedir . '/' . $namespace . '.feed');
        }
    }
    
    /**
     * Read cache file
     * 
     * This function also extracts the namespace from the cache filename.
     * 
     * @param string $filename
     * 
     * @return void
     * 
     * @access protected
     */
    protected function readCacheFile($filename) {
        //get namespace
        preg_match('/\/([a-z]+)\.feed$/i', $filename, $namespace);
        //read file
        if (is_readable($filename)) {
            $content = file_get_contents($filename);
            if ($content) {
                $this->cache[$namespace[1]] = unserialize($content);
            }
        } else {
            $this->cache[$namespace[1]] = array();
        }
    }
    
    /**
     * Save cache
     * 
     * If $namespace is not defined, iterate through the active cache and save
     * it all.
     * 
     * @param string|null $namespace
     * 
     * @return void
     * 
     * @access public
     */
    public function save($namespace = null) {
        if (is_null($namespace)) {
            foreach($this->cache as $namespace => $feed) {
                file_put_contents(
                        $this->basedir . "/{$namespace}.feed", 
                        serialize($feed)
                );
            }
        } elseif (!empty($this->cache[$namespace])) {
            file_put_contents(
                    $this->basedir . "/{$namespace}.feed", 
                    serialize($this->cache[$namespace])
            );
        }
    }
    
    /**
     * Check environment
     * 
     * Check if cache folder exists and is writable. Otherwise add new runtime
     * notification to the Notification center.
     * 
     * @return void
     * 
     * @access public
     */
    public static function checkEnv() {
        //check if there is a social feed cache folder
        if (!is_writable(\WP_CONTENT_DIR . '/myjive-cache')) {
            Notification::getInstance()->add(
                '200', 
                sprintf(
                    __('Directory %s does not exist or is not writable', MYJIVE_KEY), 
                    \WP_CONTENT_DIR . '/myjive-cache'
                ),
                true
            );
        }
    }

    /**
     * Get single instance of itself
     * 
     * @return Myjive\Core\Cache
     * 
     * @access public
     */
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

}