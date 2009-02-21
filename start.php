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
 * DEPRECATED
 *
 * this file is a compatability shim for old index.php implementations
 */

require_once('SITE/framework/SiteLoader.php');

class CompatSite extends Site
{
    public function onConfig()
    {
        onConfig($this->config);
    }
}
Site::set('CompatSite');

$type = (isset($APP) && $APP) ? $APP : 'web';
switch ($type) {
    case 'web-lite':
        Site::Site()->doWebLite();
        break;
    case 'web':
        Site::Site()->doWeb();
        break;
    case 'test':
        Site::Site()->doTests();
        break;
    case 'cli':
        Site::Site()->doCli();
        require_once dirname(__FILE__) . '/cli.php';
        break;
}

?>
