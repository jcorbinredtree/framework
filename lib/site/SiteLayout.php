<?php
/**
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
 */

/**
 * Describes the layout of the sites assets on disk
 */
class SiteLayout
{
    /**
     * The site writable area
     *
     * @default Loader::$Base/SITE/writable
     * @var string
     */
    public $writableDir;

    /**
     * Where to cache things
     *
     * @default $writableDir/cache
     * @var string
     */
    public $cacheDir;

    /**
     * Where to store the logs
     *
     * @default $writableDir/logs
     * @var string
     */
    public $logDir;

    /**
     * Holds keyed cache directories, contains things like:
     *   'template' => "$writableDir/template"
     *
     * In other words, subclasses can plug this in their constructor to change
     * the sites sense of where things should be cached; note, if they do so,
     * they need to guarantee that the directory exists and is writable, no
     * other checking will be done as for normal cache areas.
     *
     * @var array
     */
    protected $cacheAreas;

    /**
     * @var Site
     */
    protected $site;

    /**
     * Constructor
     *
     * @param site Site
     */
    public function __construct(Site &$site)
    {
        $this->site = $site;

        $this->writableDir = Loader::$Base.'/SITE/writable';
        $this->cacheDir = $this->writableDir.'/cache';
        $this->logDir = $this->writableDir.'/logs';
        $this->cacheAreas = array();
    }

    protected function _mkdir($dir, $mode=0777, $recurse=true) {
        if (! is_dir($dir) && ! @mkdir($dir, $mode, $recurse)) {
            throw new RuntimeException("Couldn't create $dir");
        }
    }

    protected function _writable($dir)
    {
        if (! is_writable($dir)) {
            throw new RuntimeException("$dir isn't writable");
        }
    }

    protected function checkWritable()
    {
        $this->_mkdir($this->writableDir);
        $this->_writable($this->writableDir);
    }

    public function getCacheArea($area=null)
    {
        if (! isset($area)) {
            $this->checkWritable();
            $this->_mkdir($this->cacheDir);
            $this->_writable($this->cacheDir);
            return $this->cacheDir;
        }
        if (! array_key_exists($area, $this->cacheAreas)) {
            $this->checkWritable();

            $dir = "$this->cacheDir/$area";
            $this->_mkdir($dir);
            $this->_writable($dir);
            $this->cacheAreas[$area] = $dir;
        }
        return $this->cacheAreas[$area];
    }

    public function setupLogDir()
    {
        $this->checkWritable();
        $this->_mkdir($this->logDir);
        $this->_writable($this->logDir);
        return $this->logDir;
    }
}

?>
