<?php

/**
 * FrameworkCompiler class definition
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
 * The Initial Developer of the Original Code is
 * Brandon Prudent <framework@redtreesystems.com>. All Rights Reserved.
 *
 * @category     TemplateSystem
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2009 Red Tree Systems, LLC
 * @version      3.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * FrameworkCompiler
 *
 * This class overrides the default php-stl Compiler to provide more functionality
 */
class FrameworkCompiler extends PHPSTLCompiler
{
    protected static $proxy;
    public static function getParamsProxy()
    {
        if (! isset(self::$proxy)) {
            self::$proxy = new ParamsProxyStub();
        }
        return self::$proxy;
    }

    // Framework Template preamble
    protected function writeTemplateHeader()
    {
        parent::writeTemplateHeader(array(
            'Framework Version' => Loader::$FrameworkVersion
        ));
        $tsys = Site::getModule('TemplateSystem');
        $this->write('<?php '.
            "if (isset(\$this->page)) {\n".
            "  \$page = \$this->page;\n".
            "} else {\n".
            "  \$page = Site::getPage();\n".
            "}\n".
        ' ?>');
        $this->write("<?php \$params = ".__CLASS__."::getParamsProxy(); ?>");
    }
}

/**
 * Used to transform expressions like:
 *   ${params.post.foo}
 *   ${params.post('foo', 'default')}
 * Into calls like:
 *   Params::post('foo')
 *   Params::post('foo', 'default')
 *
 * An instance of this class is created at the top of every template and set
 * to $params
 */
class ParamsProxyStub
{
    public function __get($type)
    {
        if (! property_exists($this, $type)) {
            $this->type = new ParamsProxy($type);
        }
        return $this->type;
    }

    public function __call($name, $args)
    {
        $this->$name->call($args);
    }
}

class ParamsProxy
{
    private $call;

    public function __construct($type)
    {
        $this->call = array('Params', $type);
        if (! is_callable($this->call)) {
            throw new BadMethodCallException(
                "No such method Params::$type"
            );
        }
    }

    public function call($args)
    {
        return call_user_func_array($this->call, $args);
    }

    public function __get($name)
    {
        return call_user_func($this->call, $name);
    }
}

?>
