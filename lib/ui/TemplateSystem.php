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

/**
 * Singleton php-stl template
 */
class TemplateSystem
{
    private static $pstl;

    public static function instance()
    {
        if (! isset(self::$pstl)) {
            global $config;
            $copt = $config->getTemplateOptions();

            $inc = array();
            if (array_key_exists('include_path', $copt)) {
                $cinc = $copt['include_path'];
                if (is_string($cinc)) {
                    $cinc = explode(',', $cinc);
                }
                assert(is_array($cinc));
                foreach ($cinc as $p) {
                    array_push($inc, "$config->absPath/$p");
                }
                unset($copt['include_path']);
            }

            array_push($inc, $config->absPath.'/SITE/local/templates');
            array_push($inc, $config->fwAbsPath.'/lib/ui/templates');

            $policy = PolicyManager::getInstance();

            self::$pstl = new PHPSTL(array_merge(array(
                'include_path'        => $inc,
                'template_class'      => 'Template',
                'compiler_class'      => 'FrameworkCompiler',
                'diskcache_directory' => $policy->getTemplatesDir()
            ), $copt));
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
