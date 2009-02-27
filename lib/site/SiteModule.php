<?php

/**
 * SiteModule definition
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
 * @category     Site
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2009 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      3.0
 * @link         http://framework.redtreesystems.com
 */

require_once 'lib/util/CallbackManager.php';

/**
 * A site module is a self contained sub-system, such as a CMS, DMS, login
 * system, etc
 *
 * Incidentally, this has absolutely nothing to do with the Module class
 */
abstract class SiteModule extends CallbackManager
{
    protected $site;

    private $required = array();
    private $optional = array();
    private $hasOptional = array();

    /**
     * Priavte utility, go away
     */
    static private function collectStaticArray(ReflectionClass $class, $name, $a=array()) {
        $c = $class;
        while ($c) {
            if ($c->hasProperty($name)) {
                $prop = $c->getProperty($name);
                if (! $prop->isStatic()) {
                    throw new RuntimeException(
                        "$c->name::\$$name isn't static"
                    );
                }
                $v = $prop->getValue();
                if (! is_array($v)) {
                    throw new RuntimeException(
                        "$c->name::\$$name isn't an array"
                    );
                }
                $a = array_unique(array_merge($a, $v));
            }
            $c = $c->getParentClass();
        }
        $r = array();
        foreach ($a as $c) {
            if (! class_exists($c)) {
                __autoload($c);
            }
            if (! class_exists($c)) {
                throw new RuntimeException("no such class $c");
            }
            if (! is_subclass_of($c, 'SiteModule')) {
                throw new RuntimeException("$c isn't a subclass of SiteModule");
            }
            $gotit = false;
            for ($i=0; $i<count($r); $i++) {
                $in =& $r[$i];
                if (is_subclass_of($in, $c)) {       // superceded
                    $gotit = true;
                    break;
                } elseif (is_subclass_of($c, $in)) { // supercedes
                    $in = $c;
                    $gotit = true;
                    break;
                }
            }
            if (! $gotit) {
                array_push($r, $c);
            }
        }
        return $r;
    }

    /**
     * Returns the list of required module names
     *
     * @return array
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Returns the list of optional module names
     *
     * @return array
     */
    public function getOptional()
    {
        return $this->optional;
    }

    /**
     * Method for subclasses to test if one of their declared optional modules
     * is present
     *
     * @param module string a module listed in $OptionalModules
     * @return bool
     */
    public function hasModule($module)
    {
        assert(array_key_exists($module, $this->hasOptional));
        return $this->hasOptional[$module];
    }

    /**
     * Creates a new SiteModule
     *
     * Collects all static $RequiredModules and $OptionalModules into the
     * $required and $optional properties.
     *
     * Verifys that all $required modules are loaded.
     *
     * @param site Site
     */
    public function __construct(Site $site)
    {
        $this->site = $site;

        // Build the list of required/optional modules
        $class = new ReflectionClass(get_class($this));
        $this->required = self::collectStaticArray(
            $class, 'RequiredModules', $this->required
        );
        $this->optional = self::collectStaticArray(
            $class, 'OptionalModules', $this->optional
        );

        foreach ($this->required as $req) {
            if (! $this->site->modules->has($req)) {
                $reqclass = new ReflectionClass($req);
                if ($reqclass->isAbstract()) {
                    throw new RuntimeException(
                        "Required module $req for module $class->name not ".
                        "loaded, can't provied a default"
                    );
                }
                $this->site->modules->add($req);
            }
        }
    }
    /**
     * Called by Site directly after every module has been instantiated and the
     * list of active modules stabalized.
     *
     * Sets $hasOptional keys for each $optional module.
     * @return void
     */
    public function initialize()
    {
        foreach ($this->optional as $opt) {
            $this->hasOptional[$opt] = $this->Sitsite->modules->has($req);
        }
    }
}

?>
