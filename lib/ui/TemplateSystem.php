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
 * @category     UI
 * @package      Utils
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2006 Red Tree Systems, LLC
 * @license      http://www.redtreesystems.com PROPRITERY
 * @version      2.0
 * @link         http://www.redtreesystems.com
 */

require_once 'extensions/php-stl/PHPSTL.php';
require_once 'lib/ui/ContentPageTemplateProvider.php';
require_once 'lib/ui/CurrentTemplateProvider.php';
require_once 'lib/ui/FrameworkCompiler.php';
require_once 'lib/ui/Template.php';
require_once 'lib/ui/tags/UiTag.php';

/**
 * Singleton php-stl template
 */
class TemplateSystem
{
    public static $TemplateClass = 'Template';
    public static $CompilerClass = 'FrameworkCompiler';

    private static $pstl;

    public static function instance()
    {
        if (! isset(self::$pstl)) {
            $site = Site::Site();
            $copt = $site->config->getTemplateOptions();

            $inc = array();
            if (array_key_exists('include_path', $copt)) {
                $inc = Site::pathArray($copt['include_path']);
                unset($copt['include_path']);
            }

            $content = array();
            if (array_key_exists('contentpage_path', $copt)) {
                $content = Site::pathArray($copt['contentpage_path']);
                unset($copt['contentpage_path']);
            }

            // TODO maybe we shouldn't add local paths here at all, leave that
            // up to the config
            array_push($inc, SiteLoader::$LocalPath.'/templates');
            array_push($inc, SiteLoader::$FrameworkPath.'/templates');
            array_push($content, SiteLoader::$LocalPath.'/content');
            $nos = false;
            if (array_key_exists('contentpage_noshared_content', $copt)) {
                $nos = (bool) $copt['contentpage_noshared_content'];
                unset($copt['contentpage_noshared_content']);
            }
            if (! $nos) {
                array_push($content, SiteLoader::$FrameworkPath.'/content');
            }

            $policy = PolicyManager::getInstance();

            self::$pstl = new PHPSTL(array_merge(array(
                'contentpage_path'    => $content,
                'include_path'        => $inc,
                'template_class'      => self::$TemplateClass,
                'compiler_class'      => self::$CompilerClass,
                'diskcache_directory' => $policy->getTemplatesDir()
            ), $copt));
            self::$pstl->addProvider(new ContentPageTemplateProvider(self::$pstl));
            self::$pstl->addProvider(new CurrentTemplateProvider(self::$pstl));
        }
        return self::$pstl;
    }

    public static function load($resource)
    {
        return self::instance()->load($resource);
    }

    public static function process($resource, $args=null)
    {
        return self::instance()->process($resource, $args);
    }
}

?>
