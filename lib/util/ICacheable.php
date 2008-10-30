<?php

/**
 * ICacheable class definition
 *
 * PHP version 5
 *
 * LICENSE: The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * 
 * The Original Code is Red Tree Systems Code.
 *
 * The Initial Developer of the Original Code is Red Tree Systems, LLC. All Rights Reserved.
 *
 * @category     Core
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.1
 * @link         http://framework.redtreesystems.com
 */

/**
 * An interface to implement caching
 *
 * @see Cacher
 * @category     Cache
 * @package      Core
 */
interface ICacheable {
    /**
     * Determines if the object is able to be cached
     *
     * @access public
     * @return boolean true if the object is cacheable
     */
    public function isCacheable();
    
    /**
     * Determines if the object should be retrieved from the cache
     *
     * @access public
     * @param int $cacheModifiedTime the last time (as unix time)
     * the cache was modified for this item
     * @return boolean true if the object should be retrieved from the cache 
     */
    public function useCache($cacheModifiedTime);
}

?>
