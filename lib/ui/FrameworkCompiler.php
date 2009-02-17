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
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * FrameworkCompiler
 *
 * This class overrides the default php-stl Compiler to provide more functionality
 */
class FrameworkCompiler extends Compiler
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Cache template compilation in a site specific place
        $policy = PolicyManager::getInstance();
        $this->setCacheDirectory($policy->getTemplatesDir());

        parent::__construct(Compiler::TYPE_BUILTIN);
    }

    /**
     * Framework Template preamble
     */
    protected function writeTemplateHeader()
    {
        $this->write('<?php global $current,$config; ?>');
        $this->write("<?php if (isset(\$this->page)) {\n");
        $this->write("  \$page = \$this->page;\n");
        $this->write("} else {\n");
        $this->write("  \$page = WebPage::getCurrent();\n");
        $this->write('} ?>');
    }

    /**
     * Specifies the replacement rules for this template
     *
     * @param string $output
     * @return string
     */
    public function replaceRules($output)
    {
        $output = preg_replace('/[$][{](?:[=])?params[.](.+?)[.](.+?)[}]/i', 'Params::$1("$2")', $output);
        return parent::replaceRules($output);
    }
}

?>
