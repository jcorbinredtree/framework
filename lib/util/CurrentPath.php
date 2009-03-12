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
 * @category     Util
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.1
 * @link         http://framework.redtreesystems.com
 */

require_once 'lib/util/StupidPath.php';

/**
 * Represents the current path
 *
 * Instances are string equivalent so that conusmers of $current->path never
 * need not be any wiser.
 *
 * Example:
 *   $path = CurrentPath::set('bla/foo')
 * same as:
 *   $path = CurrentPath::set(Loader::$Base.'/bla/foo')
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

    private static $current=null;

    public static function get()
    {
        return self::$current;
    }

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
            if (is_string($path) || $path instanceof StupidPath) {
                $path = new self($path);
            } elseif (! $path instanceof CurrentPath) {
                throw new InvalidArgumentException('Invalid path');
            }
        }

        $oldPath = self::$current;
        self::$current = $path;
        return $oldPath;
    }

    /**
     * Constructs a new curent url
     *
     * @param path string
     */
    public function __construct($path, $url=null)
    {
        if ($path instanceof StupidPath) {
            $this->path = $path;
        } else {
            $path = realpath($path);

            // Strip trailing slash
            if ($path[strlen($path)-1] == '/') {
                $path = substr($path, 0, strlen($path)-1);
            }

            $this->path = new StupidPath(explode('/', $path));
        }

        if ($url instanceof StupidPath) {
            $this->url = $url;
        } else {
            if (! isset($url)) {
                $url = (string) $this->path;
            }

            $bl = strlen(Loader::$Base);
            if (substr($url, 0, $bl) == Loader::$Base) {
                $url = substr($url, $bl);
                $url = explode('/', $url);

                // TODO Intropsect Loader variables and the site's url base, and
                // determine the framework path thusly
                if (count($url) >= 1 && $url[0] == 'framework') {
                    $url = array_slice($url, 1);
                } elseif (count($url) >= 2 && $url[0] == '' && $url[1] == 'framework') {
                    $url = array_slice($url, 2);
                }

                if ($url[0] == '') {
                    array_shift($url);
                }
                $this->url = new StupidPath(array_merge(
                    explode('/', Site::Site()->url),
                    $url
                ));
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
