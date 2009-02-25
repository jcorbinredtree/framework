<?php

/**
 * AppConstants definition
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
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Holds constants, mostly parameter keys
 *
 * There should be only one instance of this class throughout the platform,
 * but is not made into a singleton class.
 *
 * @static
 * @package        Core
 */
class AppConstants
{
    /**
     * The key used for passing component back and forth
     *
     * @var string
     */
    const COMPONENT_KEY = '_c';

    /**
     * The key used for passing action back and forth
     *
     * @var string
     */
    const ACTION_KEY = '_a';

    /**
     * The key used for passing stage back and forth
     *
     * @var string
     */
    const STAGE_KEY = '_s';

    /**
     * The key used for determining secure requests
     *
     * @var string
     */
    const SECURE_KEY = '_se';

    /**
     * The key used to store the application data
     */
    const APPLICATION_DATA_KEY = '__application_data';

    /**
     * The key used to store class information
     *
     * @var string
     */
    const CLASSMAP_KEY = 'applicationclassmap';

    /**
     * The key used to store application lifecycle objects
     *
     * @var string
     */
    const LIFECYCLE_KEY = 'lifecycleobjects';

    /**
     * The key used to store application lifecycle objects
     *
     * @var string
     */
    const FILES_KEY = 'fileobjects';

    /**
     * Component key
     *
     * @var string
     */
    const COMPONENT_FILE_KEY = '_cfk';

    /**
     * The key used in saved requests
     *
     * @var string
     */
    const SAVED_REQUEST_KEY = '_sr';

    /**
     * The key used to save the Current structure
     *
     * @var string
     */
    const LAST_CURRENT_KEY = '_lc';

    /**
     * Language key
     *
     * @var string
     */
    const LANGUAGE_KEY = '_la';

    /**
     * Popup key
     *
     * @var string
     */
    const POPUP_KEY = '_po';

    /**
     * Search key
     *
     * @var string
     */
    const SEARCH_KEY = '_sh';

    /**
     * Keyword key
     *
     * @var string
     */
    const KEYWORD_KEY = '_keyword';

    /**
     * Language cookie name
     *
     * @var string
     */
    const LANGUAGE_COOKIE = 'lang';

    /**
     * Session time key
     *
     * @var string
     */
    const TIME_KEY = '__time';

    /**
     * private constructor
     *
     */
    private function __construct() { }
}

?>
