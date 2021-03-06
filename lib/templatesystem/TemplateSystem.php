<?php

/**
 * TemplateSystem class definition
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
 * @category     TemplateSystem
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2009 Red Tree Systems, LLC
 * @version      3.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Singleton php-stl template
 */
class TemplateSystem extends SiteModule
{
    public static $TemplateClass = 'Template';
    public static $CompilerClass = 'FrameworkCompiler';

    public static $OptionalModules = array(
        'PageSystem'
    );

    private $pstl;

    public function initialize()
    {
        parent::initialize();

        require_once "$this->dir/php-stl/PHPSTL.php";
        require_once "$this->dir/FrameworkCompiler.php";
        require_once "$this->dir/Template.php";
        if ($this->hasModule('PageSystem')) {
            require_once "$this->dir/ContentPageTemplateProvider.php";
        }

        PHPSTL::registerNamespace(
            'urn:redtree:ui:form:v1.0',
            'TemplateFormHandler',
            dirname(__FILE__).'/TemplateFormHandler.php'
        );

        if ($this->hasModule('PageSystem')) {
            PHPSTL::registerNamespace(
                'urn:redtree:ui:page:v1.0',
                'TemplatePageHandler',
                dirname(__FILE__).'/TemplatePageHandler.php'
            );
        }

        $this->site->addCallback('onPostConfig', array($this, 'onPostConfig'));
    }

    public function onPostConfig()
    {
        $copt = $this->site->config->getGroup('templatesystem')->toArray();

        $inc = array();
        if (array_key_exists('include_path', $copt)) {
            $inc = Site::pathArray($copt['include_path']);
            unset($copt['include_path']);
        }

        // TODO maybe we shouldn't add local paths here at all, leave that
        // up to the config
        array_push($inc, Loader::$LocalPath);
        array_push($inc, Loader::$FrameworkPath);

        $nos = false;
        if (array_key_exists('contentpage_noshared_content', $copt)) {
            $nos = (bool) $copt['contentpage_noshared_content'];
            unset($copt['contentpage_noshared_content']);
        }

        if ($this->hasModule('PageSystem')) {
            $content = array();
            if (array_key_exists('contentpage_path', $copt)) {
                $content = Site::pathArray($copt['contentpage_path']);
                unset($copt['contentpage_path']);
            }
            array_push($content, Loader::$LocalPath.'/content');
            if (! $nos) {
                array_push($content, Loader::$FrameworkPath.'/content');
            }
            $copt['contentpage_path'] = $content;
        }

        $this->pstl = new PHPSTL(array_merge(array(
            'include_path'        => $inc,
            'template_class'      => self::$TemplateClass,
            'compiler_class'      => self::$CompilerClass,
            'diskcache_directory' => $this->site->layout->getCacheArea('template')
        ), $copt));
        if ($this->hasModule('PageSystem')) {
            $this->pstl->addProvider(new ContentPageTemplateProvider($this->pstl));
        }
    }

    public function getPHPSTL()
    {
        if (! isset($this->pstl)) {
            throw new RuntimeException('php-stl not initialized');
        }
        return $this->pstl;
    }

    public function load($resource)
    {
        return $this->getPHPSTL()->load($resource);
    }

    public function process($resource, $args=null)
    {
        return $this->getPHPSTL()->process($resource, $args);
    }
}

?>
