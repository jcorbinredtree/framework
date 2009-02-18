<?php

/**
 * CurrentPath class definition
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
 * @category     Application
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.1
 * @link         http://framework.redtreesystems.com
 */

/**
 * Represents the current path
 *
 * Instances are string equivalent so that conusmers of $current->path never
 * need not be any wiser.
 *
 * Example:
 *   $path = CurrentPath::set('bla/foo')
 * same as:
 *   $path = CurrentPath::set($config->absUri.'/bla/foo')
 *
 * echo $path; // prints /path/to/site/bla/foo
 * echo $path->up(); // prints /path/to/site/bla
 *
 * echo $path->url; // prints /bla/foo
 * echo $path->up(); // prints /bla
 */
class CurrentPath
{
    /**
     * The file path
     *
     * @var StupidPath
     */
    public $path=null;

    /**
     * The url corresponding to path
     *
     * @var StupidPath
     */
    public $url=null;

    /**
     * Sets the current->path. A relative or absolute path
     * may be used.
     *
     * @static
     * @access public
     * @param string $path the value current->path should be set to
     * @return string the old value of current->path
     */
    public static function set($path)
    {
        if (isset($path)) {
            if (is_string($path) || is_a($path, 'StupidPath')) {
                $path = new self($path);
            } elseif (! is_a($path, 'CurrentPath')) {
                throw new InvalidArgumentException('Invalid path');
            }
        }

        global $current;
        $oldPath = $current->path;
        $current->path = $path;
        return $oldPath;
    }

    /**
     * Constructs a new curent url
     *
     * @param path string
     */
    public function __construct($path, $url=null)
    {
        if (is_a($path, 'StupidPath')) {
            $this->path = $path;
        } else {
            $path = realpath($path);

            // Strip trailing slash
            if ($path[strlen($path)-1] == '/') {
                $path = substr($path, 0, strlen($path)-1);
            }

            $this->path = new StupidPath(explode('/', $path));
        }

        if (is_a($url, 'StupidPath')) {
            $this->url = $url;
        } else {
            if (! isset($url)) {
                $url = (string) $path;
            }

            global $config;
            $base = $config->absPath;
            if (substr($url, 0, strlen($base)) == $base) {
                $url = explode('/', substr($url, strlen($base)));

                if ($url[0] == 'SITE') {
                    $url = array_slice($url, 2);
                } elseif ($url[0] == '' && $url[1] == 'SITE') {
                    $url = array_slice($url, 3);
                }

                if ($url[0] != '') {
                    $url = array_merge(explode('/', $config->absUriPath), $url);
                }

                $this->url = new StupidPath($url);
            }
        }
    }

    /**
     * Returns string representation of $path property
     */
    public function __tostring()
    {
        return (string) $this->path;
    }

    /**
     * Rolls both path and url up by n and returns a new self
     *
     * @see StupidPath::up
     */
    public function up($n=1)
    {
        return new self($this->path->up($n), $this->url->up($n));
    }

    /**
     * Descends into bothp and url and returns a new self
     *
     * @see StupidPath::down
     */
    public function down()
    {
        $args = func_get_args();
        return new self($this->path->down($args), $this->url->down($args));
    }
}

?>
