<?php

/**
 * SiteModuleLoader definition
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
 * @version      2.0
 * @link         http://framework.redtreesystems.com
 */

require_once 'lib/site/SiteModule.php';

class SiteModuleLoader
{
    /**
     * List of SiteModules active in this site
     *
     * @var array of SiteModule
     */
    private $modules;

    /*
     * State arrays
     * They form a gap-buffer representing where Site is at in the module
     * loading process. What's a gap buffer you say? Here's a quick data
     * structure refresher:
     *   |          List of modules being loaded          |
     *   | modulesLoaded                   modulesLoading |
     *   | 0 ...   #-1 #                   0 ...        # |
     *   | ^ Done      ^ currently loading ^ Yet to load  |
     * In words:
     *   modulesLoaded contains modules already loaded and
     *   the tail of modulesLoaded is the module currently being loaded
     *   modulesLoading contains modules yet to be loaded
     *
     * Oh and, both lists contain only strings, early on we deal with class
     * names in add/has
     *
     * They're used by has and add so that both methods can be used flexibly
     * by module classes early on to bring in other modules as a dependancy
     * and so on
     *
     * After the constructor is done, they're unset
     *
     * And yes, they should start out null since we test isset() on hem later
     * to see if we're in the constructor or not when add/has are called
     */
    private $modulesLoading = null;
    private $modulesLoaded  = null;
    private $site;

    /**
     * Loads the modules
     *
     * @param modules array of class name strings
     * @return void
     */
    public function __construct(Site $site)
    {
        $this->site = $site;
        $this->site->modules = $this;

        $this->modules = array();

        // Collect all static $Modules arrays
        $class = new ReflectionClass(get_class($this->site));
        $modules = array();
        while ($class) {
            try {
                $mod = $class->getStaticPropertyValue('Modules');
                if (! isset($mod)) {
                    continue;
                }
                if (! is_array($mod)) {
                    throw new InvalidArgumentException(
                        "$class->name::\$Modules is not an array"
                    );
                }
                $modules = array_merge($modules, $mod);
                unset($mod);
            } catch (ReflectionException $e) {
            }
            $class = $class->getParentClass();
        }

        // Now prune out duplicates and leave only the most specific subclass
        $modules = $this->pruneModuleList($modules);

        // Now try to add each module
        $this->modulesLoading = $modules;
        $this->modulesLoaded = array();
        unset($modules);
        while (count($this->modulesLoading)) {
            $module = array_pop($this->modulesLoading);
            array_push($this->modulesLoaded, $module);
            array_push($this->modules, new $module($this->site));
        }
        $this->modulesLoading = null;
        $this->modulesLoaded  = null;

        // Now re-order the modules in dependency order
        for ($i=0; $i<count($this->modules); $i++) {
            $module = $this->modules[$i];
            foreach ($module->getRequired() as $dep) {
                for ($j=0; $j<count($this->modules); $j++) {
                    if ($this->modules[$j] instanceof $dep) {
                        if ($i < $j) {
                            array_splice($this->modules, $i, 0,
                                array_splice($this->modules, $j, 1)
                            );
                            $i++;
                        }
                        break;
                    }
                }
            }
        }

        // Now initialize each module
        foreach ($this->modules as $module) {
            $module->initialize();
        }
    }

    /**
     * Removes duplicates
     * Keeps only the most specifc subclass of any given module
     * Makes sure every class is loaded and is indeed a subclass of SiteModule
     * Does the dishes and laundary too?
     */
    private function pruneModuleList($modules)
    {
        $modules = array_unique($modules);
        $idx = array();
        $new = array();
        foreach ($modules as $m) {
            if (! class_exists($m)) {
                __autoload($m);
            }
            if (! class_exists($m)) {
                throw new InvalidArgumentException("no such class $m");
            }
            if (! is_subclass_of($m, 'SiteModule')) {
                throw new InvalidArgumentException(
                    "$m isn't a subclass of SiteModule"
                );
            }
            if (! array_key_exists($m, $idx)) {
                $c = $m;
                $gotit = false;
                while ($c != 'SiteModule') {
                    if (array_key_exists($c, $idx)) {
                        if ($idx[$c] > -1) {
                            array_splice($new, $idx[$c], 1, $m);
                            $idx[$m] = $idx[$c];
                            $idx[$c] = -1;
                        }
                        $gotit = true;
                        break;
                    }
                    $c = get_parent_class($c);
                }
                if (! $gotit) {
                    $idx[$m] = array_push($new, $m)-1;
                    $c = get_parent_class($m);
                    while ($c != 'SiteModule') {
                        $idx[$c] = -1;
                        $c = get_parent_class($c);
                    }
                }
            }
        }
        return $new;
    }

    /**
     * Adds a module to the site
     *
     * Modules are designed to be singleton within a site, subsequent calls to
     * add a module, or any module that is a superclass of a pre-existing
     * module, will be a no-op.
     *
     * If this is called after the site is fully constructed, the results are
     * undefined; subclasses should call it in their constructor BEFORE invoking
     * the parent::__construct if at all.
     *
     * The normal mechanism is simply to define a static $Modules array in the
     * Site subclass containing strings of classNames.
     *
     * @param module SiteModule or string clas name
     */
    public function add($module)
    {
        if (isset($this->modulesLoading)) {
            assert(is_string($module));
            foreach ($this->modulesLoaded as &$m) {
                if ($module == $m) {
                    return;
                }
                $mod_class = $module;
                while ($mod_class != 'SiteModule') {
                    if (
                        $m == $mod_class ||
                        is_subclass_of($mod_class, $m) ||
                        is_subclass_of($m, $mod_class)
                    ) {
                        throw new RuntimeException(
                            "duplicate module ".get_class($module)." of $m"
                        );
                    }
                    $mod_class = get_parent_class($mod_class);
                }
            }
            unset($m);
            $gotit = false;
            for ($i=0; $i<count($this->modulesLoading); $i++) {
                $m =& $this->modulesLoading[$i];
                $mod_class = $module;
                if ($m == $module || is_subclass_of($m, $module)) {
                    $gotit = true;
                } elseif (is_subclass_of($module, $m)) {
                    $m = $mod_class;
                    $gotit = true;
                }
            }
            if (! $gotit) {
                array_push($this->modulesLoading, $module);
            }
        } else {
            assert(is_object($module));
            assert($module instanceof SiteModule);
            foreach ($this->modules as &$m) {
                if ($module === $m) {
                    return;
                }
                $m_class = get_class($m);
                $mod_class = get_class($module);
                while ($mod_class != 'SiteModule') {
                    if (
                        $m_class == $mod_class ||
                        is_subclass_of($m_class, $mod_class)
                    ) {
                        throw new RuntimeException(
                            "duplicate module ".get_class($module)." of $m_class"
                        );
                    }
                    $mod_class = get_parent_class($mod_class);
                }
            }
            array_push($this->modules, $module);
        }
    }

    /**
     * Tests whether the site has a given module
     *
     * @param module mixed string or SiteModule object
     * @return boolean
     */
    public function has($module)
    {
        if (is_object($module)) {
            $module = get_class($module);
        }
        if (! is_string($module)) {
            throw new InvalidArgumentException('not a SiteModule or string');
        }
        if (! class_exists($module)) {
            throw new InvalidArgumentException("No such class $module");
        }
        if (! is_subclass_of($module, 'SiteModule')) {
            throw new InvalidArgumentException(
                "$module not a subclass of SiteModule"
            );
        }
        if (isset($this->modulesLoading)) {
            $l = array_merge($this->modulesLoaded, $this->modulesLoading);
            foreach ($l as $mod) {
                if (
                    $mod == $module ||
                    is_subclass_of($module, $mod) ||
                    is_subclass_of($mod, $module)
                ) {
                    return true;
                }
            }
        } else {
            if (!is_array($this->modules)) throw new Exception('trace');
            foreach ($this->modules as &$m) {
                $m_class = get_class($m);
                if ($m_class == $module || is_subclass_of($m_class, $module)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns the SiteModule object added to the site for the given class,
     * this will be either of the class or a subclass of it
     *
     * @param string class name
     * @return boolean
     */
    public function get($module)
    {
        if (! is_string($module)) {
            throw new InvalidArgumentException('not a string');
        }
        if (! class_exists($module)) {
            throw new InvalidArgumentException("No such class $module");
        }
        if (! is_subclass_of($module, 'SiteModule')) {
            throw new InvalidArgumentException(
                "$module not a subclass of SiteModule"
            );
        }
        foreach ($this->modules as &$m) {
            $m_class = get_class($m);
            if ($m_class == $module || is_subclass_of($m_class, $module)) {
                return $m;
            }
        }
        throw new RuntimeException("no $module in the site");
    }
}

?>
